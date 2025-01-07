<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CompanyInfo;
use Illuminate\Support\Facades\Mail;
use App\Mail\DeliveryNoteMail;

class OrderController extends Controller
{
    public function generateDeliveryNote(Order $order)
    {
        $order = Order::with('customer', 'items.product')->findOrFail($order->id);
        $company = CompanyInfo::getDefault();
        $orderData = [];

        foreach ($order->items as $item) {
            $orderData[] = [
                'Product Name' => $item->product->name,
                'Notes' => $order->notes,
                'Weight' => $item->qty,
            ];
        }

        $totalWeight = array_sum(array_column($orderData, 'Weight'));

        $pdf = PDF::loadView('pages.rapport.pdf.bon-livraison', [
            'order' => $order,
            'orderData' => $orderData,
            'totalWeight' => $totalWeight,
            'company' => $company,
        ]);

        $year = Carbon::parse($order->created_at)->format('Y');
        $pdfDirectory = "pdf/{$year}/bons-livraison/";
        $fullPdfDirectory = storage_path("app/public/{$pdfDirectory}");

        if (!file_exists($fullPdfDirectory)) {
            mkdir($fullPdfDirectory, 0755, true);
        }

        $pdfFileName = "BL-{$order->number}.pdf";
        $pdfPath = $fullPdfDirectory . $pdfFileName;
        $pdf->save($pdfPath);

        $storageUrl = Storage::url("{$pdfDirectory}{$pdfFileName}");

        // Mise à jour des URLs
        $order->url = config('app.url') . $storageUrl;
        $order->delivery_note_url = $storageUrl;
        $order->save();

        // Envoi du mail
        Mail::to('liana.jacques@aquaphoenix.fr')
            ->cc('jacques.steeven@gmail.com')
            ->send(new DeliveryNoteMail($order, $pdfPath));

        return $pdf;
    }

    public function downloadDeliveryNote(Order $order)
    {
        $pdf = $this->generateDeliveryNote($order);
        return $pdf->stream("BL-{$order->number}.pdf");
    }
}
