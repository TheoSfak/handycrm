<?php
/**
 * Simple Excel Writer
 * Creates basic Excel XML files without external dependencies
 */

class SimpleExcelWriter {
    private $data = [];
    private $headers = [];
    private $filename;
    
    public function __construct($filename = 'export.xls') {
        $this->filename = $filename;
    }
    
    /**
     * Set headers
     */
    public function setHeaders($headers) {
        $this->headers = $headers;
    }
    
    /**
     * Add row of data
     */
    public function addRow($row) {
        $this->data[] = $row;
    }
    
    /**
     * Generate and download Excel file
     */
    public function download() {
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"{$this->filename}\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        echo $this->generateXML();
        exit;
    }
    
    /**
     * Generate Excel XML
     */
    private function generateXML() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        
        // Styles
        $xml .= '<Styles>' . "\n";
        
        // Header style
        $xml .= '<Style ss:ID="Header">' . "\n";
        $xml .= '  <Font ss:Bold="1" ss:Size="12"/>' . "\n";
        $xml .= '  <Interior ss:Color="#4472C4" ss:Pattern="Solid"/>' . "\n";
        $xml .= '  <Font ss:Color="#FFFFFF"/>' . "\n";
        $xml .= '  <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
        $xml .= '  <Borders>' . "\n";
        $xml .= '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        $xml .= '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        $xml .= '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        $xml .= '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        $xml .= '  </Borders>' . "\n";
        $xml .= '</Style>' . "\n";
        
        // Cell style
        $xml .= '<Style ss:ID="Cell">' . "\n";
        $xml .= '  <Alignment ss:Vertical="Top" ss:WrapText="1"/>' . "\n";
        $xml .= '  <Borders>' . "\n";
        $xml .= '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D0D0D0"/>' . "\n";
        $xml .= '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D0D0D0"/>' . "\n";
        $xml .= '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D0D0D0"/>' . "\n";
        $xml .= '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D0D0D0"/>' . "\n";
        $xml .= '  </Borders>' . "\n";
        $xml .= '</Style>' . "\n";
        
        $xml .= '</Styles>' . "\n";
        
        // Worksheet
        $xml .= '<Worksheet ss:Name="Sheet1">' . "\n";
        $xml .= '<Table>' . "\n";
        
        // Column widths
        $xml .= '<Column ss:Width="150"/>' . "\n"; // Customer name
        $xml .= '<Column ss:Width="200"/>' . "\n"; // Address
        $xml .= '<Column ss:Width="100"/>' . "\n"; // Phone
        $xml .= '<Column ss:Width="150"/>' . "\n"; // Other details
        $xml .= '<Column ss:Width="100"/>' . "\n"; // Maintenance date
        $xml .= '<Column ss:Width="100"/>' . "\n"; // Next maintenance
        $xml .= '<Column ss:Width="80"/>' . "\n";  // Power
        $xml .= '<Column ss:Width="200"/>' . "\n"; // Insulation
        $xml .= '<Column ss:Width="200"/>' . "\n"; // Coil resistance
        $xml .= '<Column ss:Width="100"/>' . "\n"; // Grounding
        $xml .= '<Column ss:Width="80"/>' . "\n";  // Oil V1
        $xml .= '<Column ss:Width="80"/>' . "\n";  // Oil V2
        $xml .= '<Column ss:Width="80"/>' . "\n";  // Oil V3
        $xml .= '<Column ss:Width="80"/>' . "\n";  // Oil V4
        $xml .= '<Column ss:Width="80"/>' . "\n";  // Oil V5
        $xml .= '<Column ss:Width="250"/>' . "\n"; // Observations
        $xml .= '<Column ss:Width="150"/>' . "\n"; // Photo
        
        // Headers
        if (!empty($this->headers)) {
            $xml .= '<Row ss:Height="30">' . "\n";
            foreach ($this->headers as $header) {
                $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">' . $this->escapeXML($header) . '</Data></Cell>' . "\n";
            }
            $xml .= '</Row>' . "\n";
        }
        
        // Data rows
        foreach ($this->data as $row) {
            $xml .= '<Row>' . "\n";
            foreach ($row as $cell) {
                $type = is_numeric($cell) ? 'Number' : 'String';
                $xml .= '<Cell ss:StyleID="Cell"><Data ss:Type="' . $type . '">' . $this->escapeXML($cell) . '</Data></Cell>' . "\n";
            }
            $xml .= '</Row>' . "\n";
        }
        
        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        $xml .= '</Workbook>';
        
        return $xml;
    }
    
    /**
     * Escape XML special characters
     */
    private function escapeXML($value) {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
