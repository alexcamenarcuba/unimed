<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_test_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('api_test_cases', 'case_group_id')) {
                $table->uuid('case_group_id')->nullable()->after('name');
            }
        });

        if (Schema::hasColumn('api_test_cases', 'grupo')) {
            $caseGroupsByCase = DB::table('api_test_cases as tc')
                ->join('endpoints as e', 'e.id', '=', 'tc.endpoint_id')
                ->leftJoin('api_test_case_groups as g', function ($join) {
                    $join->on('g.suite_id', '=', 'e.suite_id')
                        ->on('g.name', '=', 'tc.grupo');
                })
                ->whereNotNull('tc.grupo')
                ->select('tc.id as case_id', 'g.id as group_id', 'e.suite_id', 'tc.grupo')
                ->get();

            foreach ($caseGroupsByCase as $row) {
                $groupId = $row->group_id;

                if (!$groupId && !empty($row->grupo)) {
                    $groupId = DB::table('api_test_case_groups')
                        ->where('suite_id', $row->suite_id)
                        ->where('name', $row->grupo)
                        ->value('id');

                    if (!$groupId) {
                        $groupId = (string) Str::uuid();

                        DB::table('api_test_case_groups')->insert([
                            'id' => $groupId,
                            'suite_id' => $row->suite_id,
                            'name' => $row->grupo,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if ($groupId) {
                    DB::table('api_test_cases')
                        ->where('id', $row->case_id)
                        ->update(['case_group_id' => $groupId]);
                }
            }
        }

        Schema::table('api_test_cases', function (Blueprint $table) {
            $table->foreign('case_group_id')
                ->references('id')
                ->on('api_test_case_groups')
                ->nullOnDelete();

            if (Schema::hasColumn('api_test_cases', 'grupo')) {
                $table->dropColumn('grupo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('api_test_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('api_test_cases', 'grupo')) {
                $table->string('grupo')->nullable()->after('name');
            }
        });

        if (Schema::hasColumn('api_test_cases', 'case_group_id')) {
            $groupsByCase = DB::table('api_test_cases as tc')
                ->leftJoin('api_test_case_groups as g', 'g.id', '=', 'tc.case_group_id')
                ->whereNotNull('tc.case_group_id')
                ->select('tc.id as case_id', 'g.name as group_name')
                ->get();

            foreach ($groupsByCase as $row) {
                DB::table('api_test_cases')
                    ->where('id', $row->case_id)
                    ->update(['grupo' => $row->group_name]);
            }
        }

        Schema::table('api_test_cases', function (Blueprint $table) {
            if (Schema::hasColumn('api_test_cases', 'case_group_id')) {
                $table->dropForeign(['case_group_id']);
                $table->dropColumn('case_group_id');
            }
        });
    }
};
