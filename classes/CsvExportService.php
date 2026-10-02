<?php
/**
 * CSV Export Service
 * Streams structured CSV files with UTF-8 BOM for Excel compatibility
 */

class CsvExportService {
    /**
     * Stream CSV download directly to browser and terminate request
     *
     * @param string $filename Download filename (e.g. 'customers-2026-10-02.csv')
     * @param array $headers Column header row
     * @param iterable $rows Rows of data
     * @param string $delimiter CSV delimiter (default ',')
     * @param string $enclosure CSV field enclosure (default '"')
     * @return void
     */
    public static function stream(
        string $filename,
        array $headers,
        iterable $rows,
        string $delimiter = ',',
        string $enclosure = '"'
    ): void {
        // Clear any previous output buffers to avoid corrupted files
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $safeFilename = str_replace(['"', "\r", "\n"], '', $filename);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"; filename*=UTF-8\'\'' . rawurlencode($safeFilename));
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            exit;
        }

        // Add UTF-8 BOM for Excel Greek/Unicode support
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if (!empty($headers)) {
            fputcsv($output, $headers, $delimiter, $enclosure);
        }

        foreach ($rows as $row) {
            fputcsv($output, (array)$row, $delimiter, $enclosure);
        }

        fclose($output);
        exit;
    }
}
