<?php

namespace App\Services;

use Codedge\Fpdf\Fpdf\Fpdf;

class PDF_UTF8 extends FPDF
{
    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $txt = $this->cleanString($txt);
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
    {
        $txt = $this->cleanString($txt);
        parent::MultiCell($w, $h, $txt, $border, $align, $fill);
    }

    protected function cleanString($str)
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $str);
    }
}
