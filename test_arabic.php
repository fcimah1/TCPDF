<?php
/**
 * اختبار الخطوط العربية المتاحة في TCPDF
 */

require_once __DIR__ . '/tcpdf/tcpdf.php';

// إنشاء PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// إعدادات
$pdf->SetCreator('Arabic Font Test');
$pdf->SetAuthor('System');
$pdf->SetTitle('اختبار الخطوط العربية');
$pdf->setRTL(true);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);

// قائمة الخطوط العربية المتاحة في TCPDF
$arabicFonts = [
    'aealarabiya' => 'AE AlArabiya',
    'aefurat' => 'AE Furat',
    'almohanad' => 'Al Mohanad',
    'dejavusans' => 'DejaVu Sans (يدعم العربية)',
    'freesans' => 'FreeSans (يدعم العربية)',
    'arial' => 'Arial (إذا كان متاح)',
];

foreach ($arabicFonts as $fontName => $fontLabel) {
    $pdf->AddPage();
    
    try {
        // العنوان
        $pdf->SetFont($fontName, 'B', 20);
        $pdf->Cell(0, 15, $fontLabel, 0, 1, 'C');
        $pdf->Ln(5);
        
        
        // نص تجريبي
        $pdf->SetFont($fontName, '', 14);
        $pdf->MultiCell(0, 10, 'هذا نص تجريبي باللغة العربية لاختبار الخط', 0, 'R');
        $pdf->Ln(5);
        
        // أرقام
        $pdf->MultiCell(0, 10, 'الأرقام: 1234567890', 0, 'R');
        $pdf->Ln(5);
        
        // جدول تجريبي
        $html = '<table dir="rtl" border="1" cellpadding="5">
                    <tr style="background-color: #4CAF50; color: white;">
                        <th>العمود الأول</th>
                        <th>العمود الثاني</th>
                        <th>العمود الثالث</th>
                    </tr>
                    <tr>
                        <td>بيانات 1</td>
                        <td>بيانات 2</td>
                        <td>بيانات 3</td>
                    </tr>
                    <tr>
                        <td>100</td>
                        <td>200</td>
                        <td>300</td>
                    </tr>
                </table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        $pdf->Ln(10);
        $pdf->SetFont($fontName, '', 10);
        $pdf->Cell(0, 10, '✅ هذا الخط يعمل بشكل صحيح', 0, 1, 'C');
        
    } catch (Exception $e) {
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, '❌ Font not available: ' . $fontName, 0, 1, 'C');
    }
}

// حفظ الملف
$outputPath = __DIR__ . '/reports/arabic_fonts_test.pdf';
$pdf->Output($outputPath, 'F');

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <title>اختبار الخطوط العربية</title>
    <style>
        body {
            font-family: Arial;
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
        }
        h1 { color: #333; }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px;
        }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>✅ تم إنشاء ملف اختبار الخطوط</h1>
        <div class='success'>
            <p>تم إنشاء ملف PDF يحتوي على جميع الخطوط العربية المتاحة</p>
            <p><strong>المسار:</strong> reports/arabic_fonts_test.pdf</p>
        </div>
        <a href='reports/arabic_fonts_test.pdf' target='_blank' class='btn'>📥 فتح الملف</a>
        <a href='dashboard.php' class='btn'>← العودة للوحة التحكم</a>
    </div>
</body>
</html>";
?>
