<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderFile;
use App\Services\SecureFileStorage;
use Illuminate\Http\Request;

class OrderFileController extends Controller
{
    public function __construct(private SecureFileStorage $files) {}

    public function store(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $request->validate([
            'files' => 'required|array|max:5',
            'files.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,zip,rar,ai,psd',
        ]);

        foreach ($request->file('files') as $file) {
            $order->files()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $this->files->store($file, "designs/{$order->id}"),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return back()->with('success', __('File desain berhasil diupload.'));
    }

    public function download(Request $request, Order $order, OrderFile $file)
    {
        $this->authorize('view', $order);
        $this->ensureFileBelongsToOrder($order, $file);

        return $this->files->response($file->file_path, $file->file_name, $request->boolean('preview'));
    }

    public function destroy(Order $order, OrderFile $file)
    {
        $this->authorize('update', $order);
        $this->ensureFileBelongsToOrder($order, $file);

        $this->files->delete($file->file_path);
        $file->delete();

        return back()->with('success', __('File berhasil dihapus.'));
    }

    private function ensureFileBelongsToOrder(Order $order, OrderFile $file): void
    {
        abort_unless((int) $file->order_id === (int) $order->id, 404);
    }
}
