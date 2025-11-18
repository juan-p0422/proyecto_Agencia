<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function index()
    {
        return response()->json(Hotel::all());
    }

    public function show($id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) return response()->json(['mensaje' => 'No encontrado'], 404);
        return response()->json($hotel);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'NombreHotel' => ['required','string','max:100'],
            'Ubicacion' => ['required','string','max:150'],
        ]);

        $hotel = Hotel::create($data);
        return response()->json($hotel, 201);
    }

    public function update(Request $request, $id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) return response()->json(['mensaje' => 'No encontrado'], 404);

        $data = $request->validate([
            'NombreHotel' => ['sometimes','required','string','max:100'],
            'Ubicacion' => ['sometimes','required','string','max:150'],
        ]);

        $hotel->update($data);
        return response()->json($hotel);
    }

    public function destroy($id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) return response()->json(['mensaje' => 'No encontrado'], 404);
        $hotel->delete();
        return response()->json(['mensaje' => 'Eliminado']);
    }
}

