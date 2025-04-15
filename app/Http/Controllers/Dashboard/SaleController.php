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
                        'params' => (object)[], // force to {} instead of []
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

            Log::info("Encoded terminal message: " . $messageStr);

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
            Log::info("Attempting socket connection to terminal at {$host}:{$port}");

            $socket = fsockopen($host, $port, $errno, $errstr, 10);
            if (!$socket) {
                Log::error("Socket connection failed: $errstr ($errno)");
                return [false, "Socket connection failed: $errstr ($errno)"];
            }

            Log::info("Socket connected successfully");

            $bytesWritten = fwrite($socket, $message);
            Log::info("Sent message to terminal", [
                'bytes_written' => $bytesWritten,
                'raw_message' => $message,
                'hex_dump' => bin2hex($message),
            ]);

            // Read first response
            $response = fgets($socket);
            Log::info("Received first response from terminal", [
                'raw_response' => $response,
                'hex_response' => bin2hex($response ?: ''),
            ]);

            // Read final response (e.g., success/failure)
            $finalResponse = fgets($socket);
            Log::info("Received final response from terminal", [
                'raw_response' => $finalResponse,
                'hex_response' => bin2hex($finalResponse ?: ''),
            ]);

            // Send ACK
            $ackBytes = fwrite($socket, $ackMessage);
            Log::info("Sent ACK message to terminal", [
                'bytes_written' => $ackBytes,
                'ack_message' => $ackMessage,
                'hex_ack' => bin2hex($ackMessage),
            ]);

            // Read 'ready' confirmation
            $ready = fgets($socket);
            Log::info("Received 'ready' from terminal", [
                'ready_message' => $ready,
            ]);

            fclose($socket);
            Log::info("Socket closed");

            if (strpos($finalResponse, 'Success') !== false) {
                return [true, $finalResponse];
            }

            return [false, $finalResponse];
        } catch (\Exception $e) {
            Log::error("Exception occurred during terminal communication", [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return [false, $e->getMessage()];
        }
    }
}
