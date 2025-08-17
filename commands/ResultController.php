<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Slot;
use app\models\Result;

/**
 * ResultController for console commands
 */
class ResultController extends Controller
{
    /**
     * Auto-generate results for slots at their scheduled time
     * Usage: php yii result/auto-generate
     */
    public function actionAutoGenerate()
    {
        // Ensure we're using the correct timezone
        date_default_timezone_set('Asia/Kolkata');
        
        $currentTime = date('H:i'); // Current time in H:i format (without seconds)
        $currentTimeWithSeconds = date('H:i:s'); // For display
        $currentDateTime = date('Y-m-d H:i:s');
        $timezone = date_default_timezone_get();
        
        echo "Auto-generate command started\n";
        echo "Current timezone: {$timezone}\n";
        echo "Current time: {$currentTimeWithSeconds}\n";
        echo "Current datetime: {$currentDateTime}\n";
        echo "Checking for slots at: {$currentTime}:00\n";
        echo "Checking for auto-generate slots...\n\n";
        
        // Find slots that should auto-generate results at current time
        $slots = Slot::findAutoSlotsForTime($currentTime)->all();
        
        if (empty($slots)) {
            echo "No slots found for auto-generation at {$currentTime}:00.\n";
            return ExitCode::OK;
        }
        
        $generated = 0;
        $skipped = 0;
        
        foreach ($slots as $slot) {
            echo "Processing slot: {$slot->title} (ID: {$slot->id}) at {$slot->scheduled_time}\n";
            
            // Check if result already exists for today
            $existingResult = Result::find()
                ->where(['slot_id' => $slot->id])
                ->andWhere(['DATE(declared_at)' => date('Y-m-d')])
                ->one();
            
            if ($existingResult) {
                echo "  - Result already exists for today, skipping\n";
                $skipped++;
                continue;
            }
            
            // Generate result
            $result = Result::createAutoResult($slot, $slot->getTodayDateTime());
            
            if ($result) {
                echo "  - Generated result: {$result->result}\n";
                $generated++;
            } else {
                echo "  - Failed to generate result\n";
            }
        }
        
        echo "\nSummary:\n";
        echo "- Generated: {$generated} results\n";
        echo "- Skipped: {$skipped} results\n";
        
        return ExitCode::OK;
    }
    
    /**
     * Lock all results whose time has passed
     * Usage: php yii result/lock-expired
     */
    public function actionLockExpired()
    {
        echo "Locking expired results...\n";
        
        $currentTime = date('Y-m-d H:i:s');
        
        // Find unlocked results whose slot time has passed
        $results = Result::find()
            ->joinWith('slot')
            ->where(['locked' => 0])
            ->andWhere(['<', 'slots.scheduled_time', $currentTime])
            ->all();
        
        $locked = 0;
        
        foreach ($results as $result) {
            if ($result->lock()) {
                echo "  - Locked result ID: {$result->id} for slot: {$result->slot->title}\n";
                $locked++;
            }
        }
        
        echo "\nLocked {$locked} expired results.\n";
        
        return ExitCode::OK;
    }
    
    /**
     * Generate test results for today's slots (for testing purposes)
     * Usage: php yii result/generate-test
     */
    public function actionGenerateTest()
    {
        echo "Generating test results for today's slots...\n";
        
        $slots = Slot::findTodaySlots()->all();
        
        if (empty($slots)) {
            echo "No slots found for today.\n";
            return ExitCode::OK;
        }
        
        $generated = 0;
        
        foreach ($slots as $slot) {
            // Check if result already exists
            if ($slot->hasResultToday()) {
                echo "  - Slot '{$slot->title}' already has result for today, skipping\n";
                continue;
            }
            
            $result = new Result([
                'slot_id' => $slot->id,
                'result' => Result::generateRandomResult(),
                'declared_at' => $slot->scheduled_time,
                'is_auto' => 1,
                'locked' => 0, // Don't lock test results
            ]);
            
            if ($result->save()) {
                echo "  - Generated test result {$result->result} for slot: {$slot->title}\n";
                $generated++;
            } else {
                echo "  - Failed to generate result for slot: {$slot->title}\n";
                print_r($result->errors);
            }
        }
        
        echo "\nGenerated {$generated} test results.\n";
        
        return ExitCode::OK;
    }
    
    /**
     * Display statistics
     * Usage: php yii result/stats
     */
    public function actionStats()
    {
        // Ensure we're using the correct timezone
        date_default_timezone_set('Asia/Kolkata');
        
        echo "Lottery Results Statistics\n";
        echo "=========================\n";
        echo "Timezone: " . date_default_timezone_get() . "\n";
        echo "Current time: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Total slots
        $totalSlots = Slot::find()->count();
        $activeSlots = Slot::findActive()->count();
        
        echo "Slots:\n";
        echo "  - Total: {$totalSlots}\n";
        echo "  - Active: {$activeSlots}\n\n";
        
        // Total results
        $totalResults = Result::find()->count();
        $todayResults = Result::findTodayResults()->count();
        $autoResults = Result::find()->where(['is_auto' => 1])->count();
        $lockedResults = Result::find()->where(['locked' => 1])->count();
        
        echo "Results:\n";
        echo "  - Total: {$totalResults}\n";
        echo "  - Today: {$todayResults}\n";
        echo "  - Auto-generated: {$autoResults}\n";
        echo "  - Locked: {$lockedResults}\n\n";
        
        // Today's pending slots (no results yet)
        $todaySlots = Slot::findTodaySlots()->count();
        $pendingSlots = $todaySlots - $todayResults;
        
        echo "Today's Status:\n";
        echo "  - Total slots: {$todaySlots}\n";
        echo "  - Results declared: {$todayResults}\n";
        echo "  - Pending results: {$pendingSlots}\n";
        
        return ExitCode::OK;
    }
}
