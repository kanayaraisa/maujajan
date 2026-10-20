<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FoodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $foods = Food::latest()->paginate(10);
        return view('admin.foods.index', compact('foods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.foods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:Makanan,Minuman,Cemilan',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
        ]);
        
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('foods', 'public');
        }

        Food::create([
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imagePath,
        ]);
        return redirect()->route('foods.index')->with('success', 'Data berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function edit($id)
    {
        // Cari data makanan berdasarkan ID (sesuaikan dengan model kamu, contoh: Food)
        $food = \App\Models\Food::findOrFail($id);

        // Kembalikan ke view form edit (sesuaikan nama view kamu)
        return view('admin.foods.edit', compact('food'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(Request $request, $id)
{
    $food = Food::findOrFail($id);

    $data = $request->except('image');

    // Cek apakah user mengupload file gambar baru
    if ($request->hasFile('image')) {
        // Hapus gambar lama jika ada di storage
        if ($food->image && \Storage::exists('public/' . $food->image)) {
            \Storage::delete('public/' . $food->image);
        }

        // Simpan gambar baru ke folder storage/app/public/foods
        $imagePath = $request->file('image')->store('foods', 'public');
        $data['image'] = $imagePath;
    }

    $food->update($data);

    return redirect()->route('foods.index')->with('success', 'Data berhasil diperbarui!');
}

    /**
     * Update the specified resource in storage.
     */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Food $food)
    {
        if ($Food->image && Storage::disk('public')->exists($food->image)) {
            Storage::disk('public')->delete($food);
        }
        $food->delete(); 
    }
}
