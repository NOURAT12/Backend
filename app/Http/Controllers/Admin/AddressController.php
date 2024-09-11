<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //////// create address
    public function create(Request $request)
    {
        $validate = Validator::make(
            $request->only('description', 'section', 'town', 'city'),
            [
                'city' => 'required|string',
                'town' => 'required|string',
                'section' => 'string|nullable',
                'description' => 'string|nullable'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $address =  Address::create([
            'city' => $request->city,
            'town' => $request->town,
            'section' => $request->section ? $request->section : null,
            'description' => $request->description ? $request->description : null

        ]);
        return $this->createResponse($address);
    }

    //////// update address
    public function update(Request $request)
    {
        $validate = Validator::make(
            $request->only('id', 'description', 'section', 'town', 'city'),
            [
                'id' => 'required|exists:addresses,id',
                'city' => 'required|string',
                'town' => 'required|string',
                'section' => 'required|string',
                'description' => 'required|string'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $address =  Address::find($request->id);
        $address->update([
            'city' => $request->city,
            'town' => $request->town,
            'section' => $request->section,
            'description' => $request->description
        ]);
        return $this->updateResponse($address);
    }

    //////// get addresses
    public function list(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('town', 'city'),
            [
                'city' => 'string',
                'town' => 'string',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $address =  Address::query();
        if ($request->city) {
            if ($request->city == 1) {
                $address->groupBy('city');
                return $this->getResponse($address->get('city'));
            }
            $address->where('city', $request->city);
            if (!$request->town) {
                return $this->getResponse($address->groupBy('town')->get('town'));
            }
        }
        if ($request->town) {
            $address->where('town', $request->town);
            return $this->getResponse($address->groupBy('section')->get('section'));
        }

        return $this->getResponse($address->paginate($limt));
    }

    //////// first Or Create Address
    public function firstOrCreateAddress(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('town', 'city', 'section', 'description'),
            [
                'city' => 'string|required',
                'town' => 'string|required',
                'section' => 'string|required',
                'description' => 'string|nullable',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $address =  Address::query();
        $address->firstOrCreate([
            'description' => $request->description,
            'section' => $request->section,
            'town' => $request->town,
            'city' => $request->city,
        ]);
        return $this->getResponse($address->get(['id']));
    }
}
