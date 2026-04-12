<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Support\Facades\DB;

/**
 * Service class to handle transactional, complex logic for ordering resources.
 * This encapsulates the complex logic that was previously in the controller.
 */
class MaterialOrderService
{
    /**
     * Reorders materials for a given course using a pivot table.
     * @param Course $course
     * @param array $orderedMaterialIds An array of material IDs in the desired display order.
     * @return bool True on success, false otherwise.
     */
    public function reorderMaterials(Course $course, array $orderedMaterialIds): bool
    {
        if (empty($orderedMaterialIds)) {
            return true; // Nothing to reorder
        }

        return DB::transaction(function () use ($course, $orderedMaterialIds) {
            // 1. Clear existing orders for this course
            DB::table('course_material_order')
                ->where('course_id', $course->id)
                ->delete();

            // 2. Re-insert records in the correct sequence
            $sequence = 1;
            foreach ($orderedMaterialIds as $materialId) {
                DB::table('course_material_order')->insert([
                    'course_id' => $course->id,
                    'material_id' => $materialId,
                    'sequence_order' => $sequence,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $sequence++;
            }
            return true;
        });
    }
}