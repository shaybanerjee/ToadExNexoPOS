<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class SaleController extends Controller
{
    public function handleSale(Request $request)
    {
        try {
            $data = $request->json()->all();

            $baseAmount = $data['baseAmount'] ?? null;

            if (!$baseAmount) {
                return response()->json(['status' => 'error', 'message' => 'Missing baseAmount'], 400);
            }

            info('SHAYON RECEIVED');

            $requestId = 0; // TODO: Use UUID if needed

            // Simulate or encode terminal message
            $saleMessage = [
                'message' => 'MSG',
                'data' => [
                    'command' => 'Sale',
                    'EcrId' => '123',
                    'requestId' => (string) $requestId,
                    'data' => [
                        'params' => [], // or ['clerkId' => '1234'] if required
                        'transaction' => [
                            'baseAmount' => number_format((float) $baseAmount, 2, '.', ''),
                            'tipAmount' => '0.00',
                            'taxIndicator' => '0',
                            'allowDuplicate' => 1
                        ]
                    ]
                ]
            ];

            $messageStr = "\x02\n" . json_encode($saleMessage) . "\n\x03\n";

            [$success, $terminalResponse] = $this->sendToTerminal($messageStr);

            if (!$success) {
                return response()->json(['status' => 'error', 'message' => $terminalResponse ?: 'Sale failed'], 500);
            }

            return Response::make($terminalResponse, 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            Log::error('Sale error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function sendToTerminal($message)
    {
        $host = env('TERMINAL_HOST', '127.0.0.1');
        $port = env('TERMINAL_PORT', 12345);

        $ackMessage = "\x02\n" . json_encode(["message" => "ACK", "data" => new \stdClass()]) . "\n\x03\n";

        try {
            $socket = fsockopen($host, $port, $errno, $errstr, 10);
            if (!$socket) {
                return [false, "Socket connection failed: $errstr ($errno)"];
            }

            fwrite($socket, $message);
            $response = fgets($socket);
            $finalResponse = fgets($socket);
            fwrite($socket, $ackMessage);
            fgets($socket); // Receive 'ready'

            fclose($socket);

            if (strpos($finalResponse, 'Success') !== false) {
                return [true, $finalResponse];
            }

            return [false, $finalResponse];
        } catch (\Exception $e) {
            return [false, $e->getMessage()];
        }
    }
}
