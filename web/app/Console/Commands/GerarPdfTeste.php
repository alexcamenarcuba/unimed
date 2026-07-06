<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GerarPdfTeste extends Command
{
    protected $signature = 'test:gerar-pdf {mb=2100} {caminho=arquivo_teste.pdf}';
    protected $description = 'Gera um PDF valido com tamanho configuravel para testes de upload';

    public function handle()
    {
        $mb = (int) $this->argument('mb');
        $caminho = $this->argument('caminho');
        $targetSize = $mb * 1024 * 1024;

        $f = fopen($caminho, 'wb');

        $offsets = [];

        fwrite($f, "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n");

        $offsets[1] = ftell($f);
        fwrite($f, "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n");

        $offsets[2] = ftell($f);
        fwrite($f, "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n");

        $offsets[3] = ftell($f);
        fwrite($f, "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 6 0 R >> >> /Contents 4 0 R >>\nendobj\n");

        $content = "BT /F1 18 Tf 72 720 Td (Arquivo de teste de tamanho) Tj ET";
        $offsets[4] = ftell($f);
        fwrite($f, "4 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n");
        fwrite($f, $content);
        fwrite($f, "\nendstream\nendobj\n");

        $offsets[5] = ftell($f);
        fwrite($f, "5 0 obj\n<< /Length {$targetSize} >>\nstream\n");

        $chunk = str_repeat('0', 1024 * 1024); // 1MB
        $chunks = intdiv($targetSize, strlen($chunk));
        $remainder = $targetSize % strlen($chunk);

        $bar = $this->output->createProgressBar($chunks);
        for ($i = 0; $i < $chunks; $i++) {
            fwrite($f, $chunk);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        if ($remainder > 0) {
            fwrite($f, str_repeat('0', $remainder));
        }
        fwrite($f, "\nendstream\nendobj\n");

        $offsets[6] = ftell($f);
        fwrite($f, "6 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n");

        $xrefOffset = ftell($f);
        $n = 7;
        fwrite($f, "xref\n0 {$n}\n");
        fwrite($f, "0000000000 65535 f \n");
        for ($i = 1; $i < $n; $i++) {
            fwrite($f, sprintf("%010d 00000 n \n", $offsets[$i]));
        }

        fwrite($f, "trailer\n<< /Size {$n} /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF");

        fclose($f);

        $this->info("PDF gerado: {$caminho} (" . round(filesize($caminho) / 1024 / 1024, 2) . " MB)");
    }
}