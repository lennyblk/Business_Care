<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    // Affichage de la liste des services
    public function index()
    {
        $services = Service::all();
        return view('services', compact('services'));
    }

    // Affichage d'un service spécifique
    public function show($id)
    {
        $service = Service::findOrFail($id);
        return view('service.show', compact('service'));
    }
}
