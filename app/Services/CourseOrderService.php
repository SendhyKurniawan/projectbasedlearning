<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Assignment;
use Illuminate\Support\Facades\DB;

/**
 * Service class to handle transactional, complex logic for ordering resources.
 * This encapsulates the complex logic that was previously in the controller.
 */
class CourseOrderService
{
    /**
     * Reorders assignments for a given course using a pivot table.
     * @param Course $course
     * @param array $orderedAssignmentIds An array of assignment IDs in the desired display order.
     * @return bool True on success, false otherwise.
     */
    public function reorderAssignments(Course $course, array $orderedAssignmentIds): bool
    {
        if (empty($orderedAssignmentIds)) {
            return true; // Nothing to reorder
        }

        return DB::transaction(function () use ($course, $orderedAssignmentIds) {
            // 1. Clear existing orders for this course
            DB::table('course_assignment_order')
                ->where('course_id', $course->id)
                ->delete();

            // 2. Re-insert records in the correct sequence
            $sequence = 1;
            foreach ($orderedAssignmentIds as $assignmentId) {
                DB::table('course_assignment_order')->insert([
                    'course_id' => $course->id,
                    'assignment_id' => $assignmentId,
                    'sequence_order' => $sequence,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $sequence++;
            }
            return true;
        });
    }

    /**
     * @param string $modelName The model name (e.g., 'Material')
     * @param mixed $primaryKey The ID of the record being ordered.
     * @param array $orderedIds The ordered list of IDs.
     * @return bool
     */
    public function reorderMaterials(string $modelName, $primaryKey, array $orderedIds): bool
    {
        // This method would be generalized for other entities (like Materials)
        // For now, we focus only on assignments as per the immediate requirement.
        // Future enhancement would involve mapping model names to their respective pivot tables.
        return false;
    }
}