<?php
require_once __DIR__ . '/../tcpdf/tcpdf.php';

class ReportGenerator {
    
    /**
     * توليد تقرير PDF من بيانات Stock Moves
     * 
     * @param array $data بيانات الحركات
     * @param int $startId رقم البداية
     * @param int $endId رقم النهاية
     * @return string مسار الملف المولد
     */
    public static function generate($data, $startId, $endId) {
        // إنشاء مجلد reports إذا لم يكن موجوداً
        $reportsDir = __DIR__ . '/../reports';
        if (!is_dir($reportsDir)) {
            mkdir($reportsDir, 0777, true);
        }

        // إعداد الـ PDF مع دعم اللغة العربية
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // معلومات المستند
        $pdf->SetCreator('نظام حركات المخزون');
        $pdf->SetAuthor('النظام الآلي');
        $pdf->SetTitle('تقرير حركات المخزون');
        $pdf->SetSubject('تقرير حركات المخزون');
        
        // تعيين اتجاه النص من اليمين لليسار
        $pdf->setRTL(true);
        
        // إعدادات الهوامش
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // إضافة صفحة
        $pdf->AddPage();
        
        // استخدام خط يدعم العربية
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, 'تقرير حركات المخزون', 0, 1, 'C');
        $pdf->Ln(2);
        
        // معلومات التقرير
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(0, 6, 'تاريخ التقرير: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
        $pdf->Cell(0, 6, "نطاق المعاملات: $startId - $endId", 0, 1, 'C');
        $pdf->Cell(0, 6, 'إجمالي السجلات: ' . count($data), 0, 1, 'C');
        $pdf->Ln(5);
        
        // جدول البيانات
        $html = self::generateTable($data);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // حفظ الملف
        // $filename = "stock_moves_{$startId}_to_{$endId}_" . date('YmdHis') . ".pdf";
        $filename = "حركة_المخزون_({$startId}_to_{$endId})_" . date('Y-m-d') . ".pdf";
        $filepath = $reportsDir . '/' . $filename;
        $pdf->Output($filepath, 'F');
        
        return $filepath;
    }
    
    /**
     * إنشاء جدول HTML للبيانات
     */
    private static function generateTable($data) {
        $html = '<style>
            table {
                border-collapse: collapse;
                width: 100%;
                font-size: 8px;
                direction: rtl;
            }
            th {
                background-color: #4CAF50;
                color: white;
                font-weight: bold;
                padding: 8px;
                text-align: right;
                border: 1px solid #ddd;
            }
            td {
                padding: 6px;
                border: 1px solid #ddd;
                text-align: right;
            }
            tr:nth-child(even) {
                background-color: #f2f2f2;
            }
            tr:hover {
                background-color: #e8f5e9;
            }
        </style>';
        
        $html .= '<table dir="rtl">';
        $html .= '<thead>
                    <tr>
                        <th width="11%">رقم المعاملة</th>
                        <th width="10%">رقم الحركة</th>
                        <th width="8%">النوع</th>
                        <th width="17%">كود الصنف</th>
                        <th width="11%">كود المخزن</th>
                        <th width="13%">التاريخ</th>
                        <th width="10%">الكمية</th>
                        <th width="10%">السعر</th>
                        <th width="10%">الخصم %</th>
                    </tr>
                  </thead>';
        
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            $html .= '<td width="11%">' . htmlspecialchars($row['trans_id'] ?? '') . '</td>';
            $html .= '<td width="10%">' . htmlspecialchars($row['trans_no'] ?? '') . '</td>';
            $html .= '<td width="8%">' . htmlspecialchars($row['type'] ?? '') . '</td>';
            $html .= '<td width="17%">' . htmlspecialchars($row['stock_id'] ?? '') . '</td>';
            $html .= '<td width="11%">' . htmlspecialchars($row['loc_code'] ?? '') . '</td>';
            $html .= '<td width="13%">' . htmlspecialchars($row['tran_date'] ?? '') . '</td>';
            $html .= '<td width="10%">' . number_format($row['qty'] ?? 0, 2) . '</td>';
            $html .= '<td width="10%">' . number_format($row['price'] ?? 0, 2) . '</td>';
            $html .= '<td width="10%">' . number_format($row['discount_percent'] ?? 0, 2) . '%</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        
        return $html;
    }
}
