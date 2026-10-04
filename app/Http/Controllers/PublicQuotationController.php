<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Illuminate\Http\Request;

class PublicQuotationController extends Controller
{
    public function show(string $token)
    {
        $quotation = Quotation::where('public_token', $token)
            ->with(['user', 'customer', 'items'])
            ->firstOrFail();

        return view('quotations.public', compact('quotation'));
    }

    public function approve(Request $request, string $token)
    {
        $quotation = Quotation::where('public_token', $token)->firstOrFail();

        if ($quotation->isExpired()) {
            return back()->with('error', __('Masa berlaku penawaran ini telah berakhir. Silakan hubungi kami untuk perpanjangan penawaran.'));
        }

        if ($quotation->isApproved() || $quotation->isConverted()) {
            return back()->with('info', __('Penawaran ini sudah disetujui sebelumnya.'));
        }

        $validated = $request->validate([
            'approved_by_name' => 'required|string|max:255',
            'approval_notes'   => 'nullable|string|max:1000',
        ]);

        $notes = $quotation->notes;
        $approvalLine = "Disetujui oleh: {$validated['approved_by_name']} (" . now()->format('d/m/Y H:i') . ')';
        if (! empty($validated['approval_notes'])) {
            $approvalLine .= "\nCatatan: " . $validated['approval_notes'];
        }
        $notes = trim(($notes ? $notes . "\n\n" : '') . $approvalLine);

        $quotation->update([
            'status'           => Quotation::STATUS_APPROVED,
            'approved_at'      => now(),
            'approved_by_name' => $validated['approved_by_name'],
            'notes'            => $notes,
        ]);

        return back()->with('success', __('Terima kasih! Penawaran berhasil disetujui. Tim kami akan segera memproses pesanan Anda.'));
    }

    public function reject(Request $request, string $token)
    {
        $quotation = Quotation::where('public_token', $token)->firstOrFail();

        if ($quotation->isApproved() || $quotation->isConverted()) {
            return back()->with('error', __('Penawaran yang sudah disetujui tidak dapat ditolak. Silakan hubungi toko secara langsung.'));
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $quotation->update([
            'status'           => Quotation::STATUS_REJECTED,
            'rejected_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', __('Terima kasih atas tanggapan Anda. Kami telah mencatat penolakan penawaran ini.'));
    }
}
