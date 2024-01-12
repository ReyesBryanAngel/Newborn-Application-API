<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourierInformationRequest;
use App\Http\Requests\FeedingRequest;
use App\Http\Resources\CourierInformationResource;
use App\Http\Resources\SpecimenFormResource;
use App\Models\CourierInformation;
use App\Models\Feeding;
use App\Models\SpecimenForm;
use Illuminate\Support\Collection;
use App\Exceptions\SaveException;
use Cache;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\SpecimenTrackingRequest;
use Illuminate\Support\Facades\DB;


class SpecimenTrackingController extends Controller
{

    const RECORD_NOT_FOUND = 'Samples has been deleted at this courier record.';
    
    const SENT = 'Sent';
    
    const PENDING = 'Pending';
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function createSample(SpecimenTrackingRequest $specimenTrackingRequest)
    {
        
        $validatedData = $specimenTrackingRequest->validated();
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
        
        foreach($feedings as $feeding) {
            $exists = Feeding::where([
                'specimen_form_id' => $specimenForm->id,
                'feeding_name' => $feeding
            ])->exists();

            if(!$exists) {
                Feeding::create([
                    'specimen_form_id' => $specimenForm->id,
                    'feeding_name' => $feeding,
                    'is_selected' => 1
                ]);
            }
        }

        return response()->json([
            'code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Feeding Created!'
        ], Response::HTTP_CREATED);
    }

    public function updateFeeding(FeedingRequest $request, SpecimenForm $specimenForm)
    {
        DB::beginTransaction();

        try {
            $feedings = $request->feedings;

           if(count($feedings) == 0) {
            Feeding::where('specimen_form_id', $specimenForm->id)
            ->update(['is_selected' => false]);
           }

           $selectedProducts = Feeding::where([
               'specimen_form_id' => $specimenForm->id,
               'is_selected' => true
           ])->pluck('feeding_name')->toArray();

           $notSelected = array_diff($selectedProducts, $feedings);

           foreach($feedings as $feeding) {

               $feed = Feeding::where([
                   'specimen_form_id' => $specimenForm->id,
                   'feeding_name' => $feeding
               ])->first();

               if(!$feed) {
                Feeding::create([
                       'specimen_form_id' => $specimenForm->id,
                       'feeding_name' => $feeding,
                       'is_selected' => true
                   ]);
               } else if($feed) {
                   $feed->update(['is_selected' => true]);
               }
           }

           foreach($notSelected as $feeding) {
                $prod = Feeding::where([
                    'specimen_form_id' => $specimenForm->id,
                    'feeding_name' => $feeding
                ])->first();
                $prod->update(['is_selected' => false]);
            }

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();

            if($e instanceof Exception) {
                return response()->json([
                    'code' => $e->getCode(),
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ], $e->getCode());
            }

            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Feedings Updated.'
        ], Response::HTTP_OK);
    }


    public function courierInformation(CourierInformationRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['result'] = self::SENT;
        $courierInformation = CourierInformation::create($validatedData);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Courier information has been saved.',
            'tracking_number' => $courierInformation->tracking_number,
        ], Response::HTTP_OK);
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
        try {
            $filteredData = $request->all(); 
            $checkedItems = array_filter($filteredData, function ($data) {
                return $data['checked'] === true || $data['checked'] === 1;
            });

            if (count($checkedItems) === 0) {
                return response()->json([
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'Select atleast one sample to deliver.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                foreach ($filteredData as $data) {
                    
                    $id = $data['id'];
                    $checked = $data['checked'];
            
                    SpecimenForm::where('id', $id)->update(['checked' => $checked]);
                }
            
                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => 'Check status updated successfully',
                ], Response::HTTP_OK);
            }
        } catch(Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An error occurred while updating samples: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showSpecificSample($id)
    {
        $specimenFormId = SpecimenForm::find($id);
        $specimenFormData = new SpecimenFormResource($specimenFormId);

        if (! $specimenFormId) {
            return response()->json([
                'code' => Response::HTTP_NOT_FOUND,
                'status' => 'failed',
                'message' => self::RECORD_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        Cache::put('specimen', $specimenFormData, now()->addMinutes(60));

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
    
        Cache::put('samples', $collectSamples, now()->addMinutes(30));
    
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
        $specimenForms = SpecimenForm::all();
        $formattedSpecimenForms = SpecimenFormResource::collection($specimenForms);

        return response()->json($formattedSpecimenForms, Response::HTTP_OK);
    }

    public function showCouriers()
    {
        $courierInformations = CourierInformation::all();
        $formattedCourierInformations = CourierInformationResource::collection($courierInformations);

        return response()->json($formattedCourierInformations, Response::HTTP_OK);
    }
}
