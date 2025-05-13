<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\EventProposalController as ApiController;
use Illuminate\Support\Facades\Log;

class EventProposalController extends Controller
{
    protected $apiController;

    public function __construct()
    {
        $this->apiController = new ApiController();
    }

    public function index()
    {
        try {
            $response = $this->apiController->index();
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return redirect()->back()->with('error', $data['message'] ?? 'Une erreur est survenue');
            }

            $eventProposals = $data['data'];
            return view('dashboards.client.event_proposals.index', compact('eventProposals'));
        } catch (\Exception $e) {
            Log::error('Error in EventProposalController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue');
        }
    }

    public function create()
    {
        try {
            $response = $this->apiController->getFormData();
            $data = json_decode($response->getContent(), true);

            return view('dashboards.client.event_proposals.create', [
                'activityTypes' => $data['activityTypes'],
                'locations' => $data['locations'],
                'defaultDurations' => $data['defaultDurations']
            ]);
        } catch (\Exception $e) {
            Log::error('Error in EventProposalController@create: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue');
        }
    }

    public function store(Request $request)
    {
        try {
            $response = $this->apiController->store($request);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return redirect()->back()->with('error', $data['message'])->withInput();
            }

            return redirect()->route('client.event_proposals.index')
                           ->with('success', 'Proposition créée avec succès');
        } catch (\Exception $e) {
            Log::error('Error in EventProposalController@store: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue')->withInput();
        }
    }

    public function show($id)
    {
        try {
            $response = $this->apiController->show($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return redirect()->route('client.event_proposals.index')
                    ->withErrors(['error' => $data['message'] ?? 'Une erreur est survenue']);
            }

            $eventProposal = $data['data'];
            return view('dashboards.client.event_proposals.show', compact('eventProposal'));
        } catch (\Exception $e) {
            Log::error('Error in EventProposalController@show: ' . $e->getMessage());
            return redirect()->route('client.event_proposals.index')
                ->withErrors(['error' => 'Une erreur est survenue']);
        }
    }

    public function edit($id)
    {
        try {
            $response = $this->apiController->edit($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return redirect()->route('client.event_proposals.index')
                    ->withErrors(['error' => $data['message'] ?? 'Une erreur est survenue']);
            }

            $eventProposal = $data['data']['eventProposal'];
            $activityTypes = $data['data']['activityTypes'];
            $locations = $data['data']['locations'];

            return view('dashboards.client.event_proposals.edit', compact('eventProposal', 'activityTypes', 'locations'));
        } catch (\Exception $e) {
            Log::error('Error in EventProposalController@edit: ' . $e->getMessage());
            return redirect()->route('client.event_proposals.index')
                ->withErrors(['error' => 'Une erreur est survenue']);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $response = $this->apiController->update($request, $id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return redirect()->back()->with('error', $data['message'])->withInput();
            }

            return redirect()->route('client.event_proposals.show', $id)
                ->with('success', 'Votre demande d\'activité a été mise à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Error in EventProposalController@update: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->apiController->destroy($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return redirect()->route('client.event_proposals.index')
                    ->withErrors(['error' => $data['message'] ?? 'Une erreur est survenue']);
            }

            return redirect()->route('client.event_proposals.index')
                ->with('success', 'Votre demande d\'activité a été supprimée avec succès.');
        } catch (\Exception $e) {
            Log::error('Error in EventProposalController@destroy: ' . $e->getMessage());
            return redirect()->route('client.event_proposals.index')
                ->withErrors(['error' => 'Une erreur est survenue']);
        }
    }
}
