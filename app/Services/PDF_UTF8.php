<?php
namespace App\Services;

/**
 * Extension de FPDF avec support UTF-8 (version améliorée)
 */
class PDF_UTF8 extends \FPDF
{
    /**
     * Convertit de manière sécurisée une chaîne UTF-8 en windows-1252
     *
     * @param string $str Chaîne à convertir
     * @return string Chaîne convertie
     */
    protected function utf8_to_win1252($str) {
        // Nettoyer la chaîne en entrée
        $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
        // Utiliser un remplacement sécuritaire
        $result = "";
        // Essayer avec iconv
        if (function_exists('iconv')) {
            $result = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $str);
            // En cas d'échec avec iconv, utiliser une méthode alternative
            if ($result === false) {
                $result = $this->fallback_utf8_to_win1252($str);
            }
        } else {
            // Méthode alternative si iconv n'est pas disponible
            $result = $this->fallback_utf8_to_win1252($str);
        }
        return $result;
    }

    /**
     * Méthode alternative pour convertir UTF-8 en windows-1252
     */
    protected function fallback_utf8_to_win1252($str) {
        // Table de conversion simple pour les caractères accentués courants
        $utf8_to_win1252_table = array(
            'à' => chr(224), 'á' => chr(225), 'â' => chr(226), 'ã' => chr(227), 'ä' => chr(228),
            'ç' => chr(231), 'è' => chr(232), 'é' => chr(233), 'ê' => chr(234), 'ë' => chr(235),
            'ì' => chr(236), 'í' => chr(237), 'î' => chr(238), 'ï' => chr(239), 'ñ' => chr(241),
            'ò' => chr(242), 'ó' => chr(243), 'ô' => chr(244), 'õ' => chr(245), 'ö' => chr(246),
            'ù' => chr(249), 'ú' => chr(250), 'û' => chr(251), 'ü' => chr(252), 'ÿ' => chr(255),
            'À' => chr(192), 'Á' => chr(193), 'Â' => chr(194), 'Ã' => chr(195), 'Ä' => chr(196),
            'Ç' => chr(199), 'È' => chr(200), 'É' => chr(201), 'Ê' => chr(202), 'Ë' => chr(203),
            'Ì' => chr(204), 'Í' => chr(205), 'Î' => chr(206), 'Ï' => chr(207), 'Ñ' => chr(209),
            'Ò' => chr(210), 'Ó' => chr(211), 'Ô' => chr(212), 'Õ' => chr(213), 'Ö' => chr(214),
            'Ù' => chr(217), 'Ú' => chr(218), 'Û' => chr(219), 'Ü' => chr(220), 'Ÿ' => chr(159),
            '€' => chr(128), '‚' => chr(130), 'ƒ' => chr(131), '„' => chr(132), '…' => chr(133),
            '†' => chr(134), '‡' => chr(135), 'ˆ' => chr(136), '‰' => chr(137), 'Š' => chr(138),
            '"' => chr(147), '"' => chr(148), '•' => chr(149), '–' => chr(150), '—' => chr(151),
            '˜' => chr(152), '™' => chr(153), 'š' => chr(154), '›' => chr(155), 'œ' => chr(156),
            'ž' => chr(158)
        );
        // Remplacer les caractères spéciaux
        $str = strtr($str, $utf8_to_win1252_table);
        // Remplacer les caractères restants non-ASCII par des points d'interrogation
        $str = preg_replace('/[^\x00-\x7F]/u', '?', $str);
        return $str;
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        // Encode le texte de manière sécurisée
        $txt = $this->utf8_to_win1252($txt);
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
    {
        // Encode le texte de manière sécurisée
        $txt = $this->utf8_to_win1252($txt);
        parent::MultiCell($w, $h, $txt, $border, $align, $fill);
    }

    function Write($h, $txt, $link='')
    {
        // Encode le texte de manière sécurisée
        $txt = $this->utf8_to_win1252($txt);
        parent::Write($h, $txt, $link);
    }
}
