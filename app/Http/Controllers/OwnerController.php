<?php

namespace App\Http\Controllers;


use App\Services\Owner\OwnerService;


use App\Http\Requests\LoginOwnerRequest;
use App\Http\Requests\UpdateOwnerRequest;
use App\Http\Requests\RegisterOwnerRequest;

class OwnerController extends Controller  //user controller
{
    protected $ownerservice;
    public function __construct(OwnerService $ownerservice)
    {
        $this->ownerservice = $ownerservice;
    }

    public function register(RegisterOwnerRequest $request)
    {
        $ownerdata =   $this->ownerservice->createOwner($request);
        return response()->json($ownerdata['message'], $ownerdata['status']);
    }

    public function login(LoginOwnerRequest $request)
    {
        $ownerdata =   $this->ownerservice->LoginOwner($request);
        return response()->json(['Data And token' => $ownerdata['data']], $ownerdata['status']);
    }
    public function updateowner(UpdateOwnerRequest $request)
    {
        $updatedData = $this->ownerservice->UpdateOwner($request);
        return response()->json(["Updated Data" => $updatedData['data']], $updatedData['status']);
    }
    public function deleteowner()
    {
        $delete = $this->ownerservice->deleteOwner();
        return response()->json(["message" => $delete["message"]], $delete["status"]);
    }
    public function restore(LoginOwnerRequest $request)
    {
        $store = $this->ownerservice->restore($request);
        return response()->json(["message" => $store["message"]], $store["status"]);
    }

    public function logout()
    {
        $logout = $this->ownerservice->logoutOwner();
        return response()->json(["message" => $logout['message']], $logout['status']);
    }
}
