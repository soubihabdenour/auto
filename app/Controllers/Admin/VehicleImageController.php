<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\VehicleRepository;
use App\Services\Image\ImageProcessor;
use App\Services\Storage\StorageInterface;

/**
 * AJAX/JSON endpoints for vehicle media management.
 * Kept separate from VehicleController so the latter stays focused on
 * the HTML form lifecycle.
 */
final class VehicleImageController
{
    public function __construct(
        private VehicleRepository $vehicles,
        private ImageProcessor    $imgProcessor,
        private StorageInterface  $storage,
    ) {}

    /**
     * Multipart image upload — one file at a time. Returns JSON for AJAX.
     */
    public function upload(Request $request): Response
    {
        $vehicleId = (int) $request->route('id', 0);
        if ($vehicleId <= 0 || $this->vehicles->findRawById($vehicleId) === null) {
            return Response::json(['error' => 'Vehicle not found'], 404);
        }
        $file = $request->files['image'] ?? null;
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return Response::json(['error' => 'No file uploaded'], 400);
        }

        try {
            $result = $this->imgProcessor->processVehicleImage(
                tmpFile:   (string) $file['tmp_name'],
                mime:      (string) ($file['type'] ?? ''),
                vehicleId: $vehicleId,
            );
            $altDefault = (string) $request->input('alt', '');
            $imageId = $this->vehicles->addImage($vehicleId, [
                'path'       => $result['path'],
                'alt_ar'     => $altDefault,
                'alt_fr'     => $altDefault,
                'alt_en'     => $altDefault,
                'width'      => $result['width'],
                'height'     => $result['height'],
                'size_bytes' => $result['size_bytes'],
                'sort_order' => time(),
            ]);
            // If this is the first image, make it the cover
            $existing = $this->vehicles->imagesFor($vehicleId);
            if (count($existing) === 1) {
                $this->vehicles->setCoverImage($vehicleId, $imageId);
            }
        } catch (\Throwable $e) {
            return Response::json(['error' => $e->getMessage()], 422);
        }
        return Response::json([
            'id'        => $imageId,
            'url'       => $result['large_url'],
            'thumb_url' => $result['thumb_url'],
        ]);
    }

    public function setCover(Request $request): Response
    {
        $vehicleId = (int) $request->route('id', 0);
        $imageId   = (int) $request->input('image_id', 0);
        if ($vehicleId <= 0 || $imageId <= 0) {
            return Response::json(['error' => 'Invalid request'], 400);
        }
        try {
            $this->vehicles->setCoverImage($vehicleId, $imageId);
        } catch (\Throwable $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
        return Response::json(['ok' => true]);
    }

    public function destroy(Request $request): Response
    {
        $imageId = (int) $request->route('imageId', 0);
        $row = $this->vehicles->deleteImage($imageId);
        if ($row === null) {
            return Response::json(['error' => 'Image not found'], 404);
        }
        $this->storage->delete((string) $row['path']);
        return Response::json(['ok' => true]);
    }
}
