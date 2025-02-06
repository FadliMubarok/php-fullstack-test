<?php

namespace App\Http\Controllers;

use App\Models\MyClient;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class MyClientController extends Controller
{
    protected function errorHandle($exception)
    {
        DB::rollBack();
        Log::error($exception->getMessage());

        return response()->json(['message' => 'Gagal Memproses Data'], 404);
    }

    public function index()
    {
        $data = MyClient::latest()->get();

        $data = [
            'myclients' => $data
        ];

        return response()->json($data);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $client = new MyClient();
            $client->name = $request->name;
            $client->slug = $request->slug;
            $client->is_project = $request->is_project;
            $client->self_capture = $request->self_capture;
            $client->client_prefix = $request->client_prefix;
            $client->client_logo = $request->client_logo;
            $client->address = $request->address;
            $client->phone_number = $request->phone_number;
            $client->city = $request->city;

            $client->save();

            Redis::set($request->slug, $client);

            return response()->json(['message' => 'Berhasil Memproses Data'], 200);
        } catch (Exception $e) {
            return $this->errorHandle($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $client = MyClient::findOrFail($id);

        DB::beginTransaction();

        try {
            $client->name = $request->name;
            $client->slug = $request->slug;
            $client->is_project = $request->is_project;
            $client->self_capture = $request->self_capture;
            $client->client_prefix = $request->client_prefix;
            $client->client_logo = $request->client_logo;
            $client->address = $request->address;
            $client->phone_number = $request->phone_number;
            $client->city = $request->city;

            $client->update();

            Redis::set($request->slug, $client);

            return response()->json(['message' => 'Berhasil Memproses Data'], 200);
        } catch (Exception $e) {
            return $this->errorHandle($e->getMessage());
        }
    }

    public function view($id)
    {
        $client = MyClient::findOrFail($id);

        return response()->json(['data' => $client, 'status' => 'OK', 'message' => 'Berhasil Ambil Data'], 200);
    }

    public function delete($id)
    {
        $client = MyClient::findOrFail($id);
        Redis::del($client->slug);

        $client->delete();

        return response()->json(['data' => $client, 'status' => 'OK', 'message' => 'Berhasil Hapus Data'], 200);
    }
}
