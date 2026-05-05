<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AzureController extends Controller
{
    private string $pat;
    private string $org;
    private string $project;
    private string $assignedTo;
    private string $solicitanteField;
    private string $baseUrl;
    private string $requirementsFeatureId;

    public function __construct()
    {
        $this->pat          = config('services.azure_devops.pat', '');
        $this->org          = config('services.azure_devops.org', 'unimedpr');
        $this->project      = config('services.azure_devops.project', 'CRM');
        $this->assignedTo   = config('services.azure_devops.assigned_to', '');
        $this->solicitanteField = config('services.azure_devops.filial_field', 'Custom.Solicitante');
        $this->requirementsFeatureId = (string) config('services.azure_devops.requirements_feature_id', '38960');
        $this->baseUrl      = "https://dev.azure.com/{$this->org}/{$this->project}/_apis";
    }

    public function dashboard()
    {
        return Inertia::render('azure/pages/AzureDashboard', [
            'title' => 'Chamados Azure DevOps',
            'workItemsEndpoint' => '/tickets/work-items',
        ]);
    }

    public function requirementsDashboard()
    {
        $featureId = (int) $this->requirementsFeatureId;

        return Inertia::render('azure/pages/AzureDashboard', [
            'title' => "Requirements do Backlog (Feature #{$featureId})",
            'subtitle' => "Itens relacionados a Feature #{$featureId}",
            'workItemsEndpoint' => '/tickets/requirements/work-items',
        ]);
    }

    public function workItems(Request $request)
    {
        $forceRefresh = (bool) $request->query('refresh', false);
        $debugMode = (bool) $request->query('debug', false);
        $cacheKey = 'azure_work_items_' . md5($this->assignedTo);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        $result = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->fetchWorkItems(false);
        });

        if ($debugMode || $forceRefresh) {
            $result = $this->fetchWorkItems($debugMode);
        }

        if (!is_array($result) || ($result['items'] ?? null) === null) {
            return response()->json(['error' => 'Erro ao consultar Azure DevOps.'], 502);
        }

        if ($debugMode) {
            return response()->json($result);
        }

        return response()->json($result['items']);
    }

    public function requirementsWorkItems(Request $request)
    {
        $forceRefresh = (bool) $request->query('refresh', false);
        $debugMode = (bool) $request->query('debug', false);
        $featureId = (int) $request->query('feature_id', (int) $this->requirementsFeatureId);
        $cacheKey = 'azure_requirements_work_items_' . $featureId;

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        $result = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($featureId) {
            return $this->fetchRequirementsByFeature($featureId, false);
        });

        if ($debugMode || $forceRefresh) {
            $result = $this->fetchRequirementsByFeature($featureId, $debugMode);
        }

        if (!is_array($result) || ($result['items'] ?? null) === null) {
            return response()->json(['error' => 'Erro ao consultar Azure DevOps.'], 502);
        }

        if ($debugMode) {
            return response()->json($result);
        }

        return response()->json($result['items']);
    }

    private function fetchRequirementsByFeature(int $featureId, bool $debugMode = false): array
    {
        if ($featureId <= 0) {
            return [
                'items' => [],
                'debug' => ['error' => 'feature_id inválido'],
            ];
        }

        $wiql = "SELECT [System.Id] FROM WorkItemLinks
                 WHERE
                    [Source].[System.TeamProject] = '{$this->project}'
                    AND [Source].[System.Id] = {$featureId}
                    AND [System.Links.LinkType] = 'System.LinkTypes.Hierarchy-Forward'
                    AND [Target].[System.TeamProject] = '{$this->project}'
                      MODE (Recursive)";

        $debug = [
            'feature_id' => $featureId,
            'wiql' => preg_replace('/\s+/', ' ', trim($wiql)),
            'steps' => [],
        ];

        $wiqlMeta = [];
        $wiqlResponse = $this->azurePost(
            "{$this->baseUrl}/wit/wiql?api-version=7.0",
            ['query' => $wiql],
            $wiqlMeta
        );

        $debug['steps']['wiql'] = $wiqlMeta;

        if ($wiqlResponse === null) {
            return [
                'items' => null,
                'debug' => $debug,
            ];
        }

        $relatedIds = collect($wiqlResponse['workItemRelations'] ?? [])
            ->pluck('target.id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $debug['steps']['wiql']['related_ids_count'] = count($relatedIds);

        if (empty($relatedIds)) {
            return [
                'items' => [],
                'debug' => $debug,
            ];
        }

        $fields = [
            'System.Id',
            'System.Title',
            'System.State',
            'System.WorkItemType',
            'System.IterationPath',
            'System.AssignedTo',
            'System.CreatedDate',
            'System.ChangedDate',
            'Microsoft.VSTS.Scheduling.DueDate',
            'Microsoft.VSTS.Common.Priority',
            'Microsoft.VSTS.Common.StackRank',
            $this->solicitanteField,
        ];

        $excludedTypes = ['feature', 'features'];
        $chunks = array_chunk($relatedIds, 200);
        $allItems = [];
        $solicitanteFieldUnavailable = false;
        $fieldsForBatch = $fields;

        foreach ($chunks as $chunk) {
            $batchMeta = [];
            $batchResponse = $this->azurePost(
                "{$this->baseUrl}/wit/workitemsbatch?api-version=7.0",
                ['ids' => $chunk, 'fields' => $fieldsForBatch],
                $batchMeta
            );

            if (
                $batchResponse === null
                && !$solicitanteFieldUnavailable
                && $this->isMissingFieldError($batchMeta, $this->solicitanteField)
            ) {
                $solicitanteFieldUnavailable = true;

                $fieldsForBatch = array_values(array_filter($fields, function ($field) {
                    return $field !== $this->solicitanteField;
                }));

                $batchMeta = [];
                $batchResponse = $this->azurePost(
                    "{$this->baseUrl}/wit/workitemsbatch?api-version=7.0",
                    ['ids' => $chunk, 'fields' => $fieldsForBatch],
                    $batchMeta
                );
            }

            if ($debugMode) {
                $debug['steps']['batch'][] = array_merge($batchMeta, [
                    'ids_in_chunk' => count($chunk),
                ]);
            }

            if ($batchResponse === null) {
                continue;
            }

            foreach ($batchResponse['value'] ?? [] as $item) {
                $f = $item['fields'] ?? [];
                $workItemType = mb_strtolower(trim((string) ($f['System.WorkItemType'] ?? '')));

                if (in_array($workItemType, $excludedTypes, true)) {
                    continue;
                }

                $state = (string) ($f['System.State'] ?? '');
                if (mb_strtolower($state) === 'removed') {
                    continue;
                }

                $dueDate = $f['Microsoft.VSTS.Scheduling.DueDate'] ?? null;
                $isOverdue = $dueDate && now()->gt(\Carbon\Carbon::parse($dueDate));

                $allItems[] = [
                    'id'          => $item['id'],
                    'title'       => $f['System.Title'] ?? '',
                    'state'       => $f['System.State'] ?? '',
                    'type'        => $f['System.WorkItemType'] ?? '',
                    'sprint'      => $this->extractSprintName($f['System.IterationPath'] ?? ''),
                    'priority'    => (int) ($f['Microsoft.VSTS.Common.Priority'] ?? 0),
                    'due_date'    => $dueDate,
                    'is_overdue'  => $isOverdue,
                    'solicitante' => $f[$this->solicitanteField] ?? null,
                    'changed_at'  => $f['System.ChangedDate'] ?? null,
                    'url'         => "https://dev.azure.com/{$this->org}/{$this->project}/_workitems/edit/{$item['id']}",
                ];
            }
        }

        usort($allItems, function ($a, $b) {
            return strcmp((string) ($b['changed_at'] ?? ''), (string) ($a['changed_at'] ?? ''));
        });

        return [
            'items' => $allItems,
            'debug' => $debug,
        ];
    }

    public function debugFields(Request $request)
    {
        $id = $request->query('id');

        if (!$id) {
            $items = $this->fetchWorkItems(true);
            $id = $items['items'][0]['id'] ?? null;
        }

        if (!$id) {
            return response()->json([
                'error' => 'Nenhum work item encontrado para debug.',
            ], 404);
        }

        $meta = [];
        $item = $this->azureGet(
            "{$this->baseUrl}/wit/workitems/{$id}?api-version=7.0",
            $meta
        );

        $fieldsMeta = [];
        $catalog = $this->azureGet(
            "{$this->baseUrl}/wit/fields?api-version=7.0",
            $fieldsMeta
        );

        $candidateFields = collect($catalog['value'] ?? [])
            ->filter(function ($field) {
                $name = (string) ($field['name'] ?? '');
                $referenceName = (string) ($field['referenceName'] ?? '');

                return str_contains(mb_strtolower($name), 'solicit')
                    || str_contains(mb_strtolower($name), 'area')
                    || str_contains(mb_strtolower($referenceName), 'solicit')
                    || str_contains(mb_strtolower($referenceName), 'filial')
                    || str_contains(mb_strtolower($referenceName), 'unidade');
            })
            ->values()
            ->all();

        return response()->json([
            'work_item_id' => $id,
            'meta' => [
                'item_request' => $meta,
                'fields_request' => $fieldsMeta,
            ],
            'candidate_fields' => $candidateFields,
            'fields' => $item['fields'] ?? [],
        ]);
    }

    private function fetchWorkItems(bool $debugMode = false): array
    {
        return $this->fetchWorkItemsByTypes(['Task', 'Bug', 'Issue'], $debugMode, true, ['Closed', 'Resolved', 'Removed']);
    }

    private function fetchWorkItemsByTypes(
        array $types,
        bool $debugMode = false,
        bool $filterByAssignee = true,
        array $excludedStates = ['Closed', 'Resolved', 'Removed']
    ): array
    {
        $allowedTypes = array_map(function ($type) {
            return mb_strtolower(trim((string) $type));
        }, $types);

        $assignedTo = trim($this->assignedTo);
        $assignedFilter = $assignedTo !== ''
            ? "([System.AssignedTo] = @Me OR [System.AssignedTo] = '" . $this->escapeWiqlString($assignedTo) . "')"
            : '[System.AssignedTo] = @Me';
        $assigneeClause = $filterByAssignee ? "AND {$assignedFilter}" : '';
        $escapedTypes = array_map(function ($type) {
            return "'" . $this->escapeWiqlString((string) $type) . "'";
        }, $types);
        $escapedStates = array_map(function ($state) {
            return "'" . $this->escapeWiqlString((string) $state) . "'";
        }, $excludedStates);
        $typeFilter = '[System.WorkItemType] IN (' . implode(', ', $escapedTypes) . ')';
        $stateClause = count($escapedStates) > 0
            ? 'AND [System.State] NOT IN (' . implode(', ', $escapedStates) . ')'
            : '';

        $wiql = "SELECT [System.Id] FROM WorkItems 
                 WHERE [System.TeamProject] = '{$this->project}'
                 {$assigneeClause}
                 AND {$typeFilter}
                 {$stateClause}
                 ORDER BY [System.ChangedDate] DESC";

        $debug = [
            'org' => $this->org,
            'project' => $this->project,
            'assigned_to' => $assignedTo,
            'wiql' => preg_replace('/\s+/', ' ', trim($wiql)),
            'steps' => [],
        ];

        Log::info('Azure work-items request started', [
            'org' => $this->org,
            'project' => $this->project,
            'assigned_to' => $assignedTo,
            'debug' => $debugMode,
        ]);

        $wiqlMeta = [];

        $wiqlResponse = $this->azurePost(
            "{$this->baseUrl}/wit/wiql?api-version=7.0",
            ['query' => $wiql],
            $wiqlMeta
        );

        $debug['steps']['wiql'] = $wiqlMeta;

        if ($wiqlResponse === null) {
            Log::error('Azure WIQL request failed', $wiqlMeta);

            return [
                'items' => null,
                'debug' => $debug,
            ];
        }

        $ids = collect($wiqlResponse['workItems'] ?? [])
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        $debug['steps']['wiql']['ids_count'] = count($ids);

        Log::info('Azure WIQL executed', [
            'ids_count' => count($ids),
        ]);

        if (empty($ids)) {
            if ($debugMode) {
                $sampleMeta = [];
                $sampleWiql = "SELECT TOP 20 [System.Id] FROM WorkItems
                               WHERE [System.TeamProject] = '{$this->project}'
                               AND {$typeFilter}
                               {$stateClause}
                               ORDER BY [System.ChangedDate] DESC";

                $sampleResponse = $this->azurePost(
                    "{$this->baseUrl}/wit/wiql?api-version=7.0",
                    ['query' => $sampleWiql],
                    $sampleMeta
                );

                $debug['steps']['sample_wiql'] = $sampleMeta;
                $debug['steps']['sample_wiql']['sample_ids_count'] = count($sampleResponse['workItems'] ?? []);
            }

            return [
                'items' => [],
                'debug' => $debug,
            ];
        }

        $fields = [
            'System.Id',
            'System.Title',
            'System.State',
            'System.WorkItemType',
            'System.IterationPath',
            'System.AssignedTo',
            'System.CreatedDate',
            'System.ChangedDate',
            'Microsoft.VSTS.Scheduling.DueDate',
            'Microsoft.VSTS.Common.Priority',
            'Microsoft.VSTS.Common.StackRank',
            $this->solicitanteField,
        ];

        $chunks = array_chunk($ids, 200);
        $allItems = [];
        $solicitanteFieldUnavailable = false;
        $fieldsForBatch = $fields;

        foreach ($chunks as $chunk) {
            $batchMeta = [];
            $batchResponse = $this->azurePost(
                "{$this->baseUrl}/wit/workitemsbatch?api-version=7.0",
                ['ids' => $chunk, 'fields' => $fieldsForBatch],
                $batchMeta
            );

            if (
                $batchResponse === null
                && !$solicitanteFieldUnavailable
                && $this->isMissingFieldError($batchMeta, $this->solicitanteField)
            ) {
                $solicitanteFieldUnavailable = true;

                Log::warning('Azure custom field unavailable, retrying without it', [
                    'field' => $this->solicitanteField,
                ]);

                $fieldsForBatch = array_values(array_filter($fields, function ($field) {
                    return $field !== $this->solicitanteField;
                }));

                $batchMeta = [];
                $batchResponse = $this->azurePost(
                    "{$this->baseUrl}/wit/workitemsbatch?api-version=7.0",
                    ['ids' => $chunk, 'fields' => $fieldsForBatch],
                    $batchMeta
                );
            }

            if ($debugMode) {
                $debug['steps']['batch'][] = array_merge($batchMeta, [
                    'ids_in_chunk' => count($chunk),
                ]);
            }

            if ($batchResponse === null) {
                Log::warning('Azure batch request failed', [
                    'ids_in_chunk' => count($chunk),
                ]);
                continue;
            }

            foreach ($batchResponse['value'] ?? [] as $item) {
                $f = $item['fields'] ?? [];
                $workItemType = (string) ($f['System.WorkItemType'] ?? '');

                // Defensive guard: even if WIQL returns unexpected items, keep only allowed types.
                if (!in_array(mb_strtolower(trim($workItemType)), $allowedTypes, true)) {
                    continue;
                }

                $dueDate = $f['Microsoft.VSTS.Scheduling.DueDate'] ?? null;
                $isOverdue = $dueDate && now()->gt(\Carbon\Carbon::parse($dueDate));

                $allItems[] = [
                    'id'          => $item['id'],
                    'title'       => $f['System.Title'] ?? '',
                    'state'       => $f['System.State'] ?? '',
                    'type'        => $workItemType,
                    'sprint'      => $this->extractSprintName($f['System.IterationPath'] ?? ''),
                    'priority'    => (int) ($f['Microsoft.VSTS.Common.Priority'] ?? 0),
                    'due_date'    => $dueDate,
                    'is_overdue'  => $isOverdue,
                    'solicitante' => $f[$this->solicitanteField] ?? null,
                    'changed_at'  => $f['System.ChangedDate'] ?? null,
                    'url'         => "https://dev.azure.com/{$this->org}/{$this->project}/_workitems/edit/{$item['id']}",
                ];
            }
        }

        Log::info('Azure work-items request finished', [
            'items_count' => count($allItems),
        ]);

        return [
            'items' => $allItems,
            'debug' => $debug,
        ];
    }

    private function isMissingFieldError(array $meta, string $field): bool
    {
        $excerpt = (string) ($meta['body_excerpt'] ?? '');

        if ($excerpt === '' || $field === '') {
            return false;
        }

        return str_contains($excerpt, 'Cannot find field') && str_contains($excerpt, $field);
    }

    private function extractSprintName(string $iterationPath): string
    {
        if ($iterationPath === '') {
            return '';
        }

        $parts = explode('\\', $iterationPath);
        return end($parts) ?: $iterationPath;
    }

    private function azurePost(string $url, array $body, ?array &$meta = null): ?array
    {
        $meta = [
            'url' => $url,
            'status' => null,
            'error' => null,
            'body_excerpt' => null,
        ];

        try {
            $response = Http::withBasicAuth('', $this->pat)
                ->acceptJson()
                ->post($url, $body);

            $meta['status'] = $response->status();

            if ($response->failed()) {
                $meta['error'] = 'http_failed';
                $meta['body_excerpt'] = substr((string) $response->body(), 0, 500);
                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            $meta['error'] = $e->getMessage();
            return null;
        }
    }

    private function azureGet(string $url, ?array &$meta = null): ?array
    {
        $meta = [
            'url' => $url,
            'status' => null,
            'error' => null,
            'body_excerpt' => null,
        ];

        try {
            $response = Http::withBasicAuth('', $this->pat)
                ->acceptJson()
                ->get($url);

            $meta['status'] = $response->status();

            if ($response->failed()) {
                $meta['error'] = 'http_failed';
                $meta['body_excerpt'] = substr((string) $response->body(), 0, 500);
                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            $meta['error'] = $e->getMessage();
            return null;
        }
    }

    private function escapeWiqlString(string $value): string
    {
        return str_replace("'", "''", $value);
    }
}
