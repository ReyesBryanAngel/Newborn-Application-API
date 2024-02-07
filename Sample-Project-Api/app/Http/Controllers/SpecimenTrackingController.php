<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourierInformationRequest;
use App\Http\Requests\FeedingRequest;
use App\Http\Resources\CourierInformationResource;
use App\Http\Resources\SpecimenFormResource;
use App\Models\CourierInformation;
use App\Models\Feeding;
use Illuminate\Support\Facades\Auth;
use App\Models\SpecimenForm;
use Cache;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\SpecimenTrackingRequest;
use App\Services\Facades\SpecimenTracking;

class SpecimenTrackingController
{

    const RECORD_NOT_FOUND = 'Samples has been deleted at this courier.';
    
    const SENT = 'Sent';
    
    const PENDING = 'Pending';
    
    // public function __construct(Request $request)
    // {
    //     $this->request = $request;
    // }

    public function createSample(SpecimenTrackingRequest $specimenTrackingRequest)
    {
        
        $validatedData = $specimenTrackingRequest->validated();
        $requestId = $specimenTrackingRequest->user_id;

        if ($requestId !== Auth::id()) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'message' => 'User not found'
            ], Response::HTTP_CONFLICT);
        }

        $validatedData['user_id'] = Auth::id();
        $specimenForm = SpecimenForm::create($validatedData);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Sample has been created!',
            'specimen_id' => $specimenForm->id
        ], Response::HTTP_OK);
    }

    public function updateSample(SpecimenTrackingRequest $specimenTrackingRequest, $id)
    {
        try {
            $validatedData = $specimenTrackingRequest->validated();
            $specimenForm = SpecimenForm::findOrFail($id);
            $specimenForm->update($validatedData);
    
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Sample has been updated'
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {

            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function deleteSample($id)
    {
        try {
            SpecimenForm::where("id", $id)->delete();
            Feeding::where('specimen_form_id', $id)->delete();
    
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Sample has been deleted'
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function createFeeding(FeedingRequest $request, SpecimenForm $specimenForm)
    {
        $feedings = $request->feedings;
        try {
            SpecimenTracking::feedingCreate($feedings, $specimenForm);

            return response()->json([
                'code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Feeding Created!'
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => 'Bad Request!'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateFeeding(FeedingRequest $request, SpecimenForm $specimenForm)
    {
        $feedings = $request->feedings;
        SpecimenTracking::feedingUpdate($feedings, $specimenForm);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Feedings Updated.'
        ], Response::HTTP_OK);
    }

    public function courierInformation(CourierInformationRequest $request)
    {
        $validatedData = $request->validated();
        $requestId = $request->user_id;
        $courierInformation = SpecimenTracking::courierCreate($requestId, $validatedData);
        if (!$courierInformation) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'message' => 'User not found'
            ], Response::HTTP_CONFLICT);
        } else {
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Courier information has been saved.',
                'tracking_number' => $courierInformation->tracking_number,
            ], Response::HTTP_OK);
        }
    }

    public function sendSamples(Request $request)
    {
        $trackingNumber = $request->input('tracking_number');
        $updatedCount = SpecimenForm::where('checked',true)
            ->where('specimen_status', self::PENDING)
            ->update([
                "specimen_status" => "In Transit",
                "tracking_number" => $trackingNumber
            ]);    

        if ($updatedCount > 0) {
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Samples have been sent'
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Please put a check for samples that must be delivered',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateCheckStatus(Request $request)
    {
        $success = SpecimenTracking::checkStatusUpdate($request);

        if ($success) {
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Check status updated successfully',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Select at least one sample to deliver.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function showSpecificSample($id)
    {;
        $specimenFormId = SpecimenForm::where([
            'user_id' => Auth::id()
        ])->find($id);

        $specimenFormData = new SpecimenFormResource($specimenFormId);

        Cache::put('specimen', $specimenFormData);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Successfully get specific sample',
            'samples' => new SpecimenFormResource($specimenFormData)
        ], Response::HTTP_OK);
    }

    public function specimenRefresh() 
    {
        $sharedSpecimenData = Cache::get('specimen');
        
        if (!$sharedSpecimenData) {
            return response()->json([
                'code' => Response::HTTP_NOT_FOUND,
                'status' => 'failed',
                'message' => self::RECORD_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Specimen replicated successfully',
            'samples' => $sharedSpecimenData
        ], Response::HTTP_OK);
    }

    public function showCourierSamples($trackingNumber)
    {
        $filteredSamples = SpecimenForm::where('tracking_number', $trackingNumber)->get();
        $collectSamples = SpecimenFormResource::collection($filteredSamples);
        
    
        if ($collectSamples->isEmpty()) {
            return response()->json([
                'code' => Response::HTTP_NOT_FOUND,
                'status' => 'failed',
                'message' => self::RECORD_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
    
        Cache::put('samples', $collectSamples);
    
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Courier Sample showed successfully',
            'samples' => $collectSamples
        ], Response::HTTP_OK);
    }

    public function courierSampleRefresh()
    {
        $sharedSampleData = Cache::get('samples');
        
        if (!$sharedSampleData) {
            return response()->json([
                'code' => Response::HTTP_NOT_FOUND,
                'status' => 'failed',
                'message' => self::RECORD_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Courier Sample replicated successfully',
            'samples' => $sharedSampleData
        ], Response::HTTP_OK);
    }

    public function showAllSample()
    {
        $specimenForms = SpecimenForm::where([
            'user_id' => Auth::id()
        ])->get();

        $formattedSpecimenForms = SpecimenFormResource::collection($specimenForms);

        return response()->json($formattedSpecimenForms, Response::HTTP_OK);
    }

    public function showCouriers()
    {
        $courierInformations = CourierInformation::where(['user_id' => Auth::id()])->get();
        $formattedCourierInformations = CourierInformationResource::collection($courierInformations);

        return response()->json($formattedCourierInformations, Response::HTTP_OK);
    }
}
