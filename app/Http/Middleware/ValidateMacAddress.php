<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateMacAddress
{
    /**
     * Validate MAC address format in requests
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if request contains MAC address parameter
        $macFields = ['mac_address', 'MAC', 'mac'];
        
        foreach ($macFields as $field) {
            if ($request->has($field)) {
                $mac = $request->input($field);
                
                // Validate MAC address format
                if (!$this->isValidMacAddress($mac)) {
                    return response()->json([
                        'error' => 'Invalid MAC address format',
                        'message' => 'MAC address must be in format: AA:BB:CC:DD:EE:FF'
                    ], 422);
                }
                
                // Normalize MAC address to uppercase with colons
                $request->merge([
                    $field => $this->normalizeMacAddress($mac)
                ]);
            }
        }

        return $next($request);
    }
    
    /**
     * Validate MAC address format
     */
    private function isValidMacAddress(string $mac): bool
    {
        // Match standard MAC formats: AA:BB:CC:DD:EE:FF or AA-BB-CC-DD-EE-FF
        $pattern = '/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/';
        return preg_match($pattern, $mac) === 1;
    }
    
    /**
     * Normalize MAC address to uppercase with colons
     */
    private function normalizeMacAddress(string $mac): string
    {
        // Remove any separators and convert to uppercase
        $mac = strtoupper(str_replace([':', '-'], '', $mac));
        
        // Insert colons every 2 characters
        return implode(':', str_split($mac, 2));
    }
}
