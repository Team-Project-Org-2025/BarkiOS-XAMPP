<?php
namespace Barkios\helpers;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfHelper
{
    private $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $this->dompdf = new Dompdf($options);
    }

    public function fromHtml(string $html): string
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        return $this->dompdf->output();
    }
}

