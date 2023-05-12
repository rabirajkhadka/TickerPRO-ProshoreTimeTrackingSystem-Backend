<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Mockery\Exception;

class ClientService
{

    public static function addClient(array $validatedAddClient)
    {
        try {
            Client::create($validatedAddClient);
        } catch (ModelNotFoundException $modelNotFoundException) {
            throw new ModelNotFoundException();
        } catch (Exception $exception) {
            throw new Exception();
        }
    }
    public static function editClient(array $validatatedEditClient, $id)
    {
        try {
            $client = Client::where('id', $id)->firstorfail();
            $client->update($validatatedEditClient);
            return $client;
        } catch (ModelNotFoundException $modelNotFoundException) {
            throw new ModelNotFoundException();
        } catch (Exception $exception) {
            throw new Exception();
        }
    }
}
