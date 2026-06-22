<?php

if (! function_exists('activity')) {
    function activity(?string $logName = null)
    {
        try {
            $logger = app(\Spatie\Activitylog\PendingActivityLog::class);
            if ($logName) {
                $logger->useLog($logName);
            }
            return $logger->logger();
        } catch (\Throwable $e) {
            return new class {
                public function log(string $message): void
                {
                    \Illuminate\Support\Facades\Log::info($message);
                }
                public function __call($name, $arguments) {
                    return $this;
                }
                public function causedBy($user) {
                    return $this;
                }
                public function withProperties($properties) {
                    return $this;
                }
                public function useLog($logName) {
                    return $this;
                }
                public function logger() {
                    return $this;
                }
            };
        }
    }
}
