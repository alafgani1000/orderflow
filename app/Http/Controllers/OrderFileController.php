<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderFileController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $request->validate([
            'files'   => 'required|array|max:5',
            'files.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,zip,rar,ai,psd',
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store("designs/{$order->id}", 'public');
            $order->files()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return back()->with('success', 'File desain berhasil diupload.');
    }

    public function download(Order $order, OrderFile $file)
    {
        $this->authorize('view', $order);

        if ($file->order_id !== $order->id) {
            abort(403);
        }

        return Storage::disk('public')->download($file->file_path, $file->file_name);
    }

    public function destroy(Order $order, OrderFile $file)
    {
        $this->authorize('update', $order);

        if ($file->order_id !== $order->id) {
            abort(403);
        }

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return back()->with('success', 'File berhasil dihapus.');
    }
}
