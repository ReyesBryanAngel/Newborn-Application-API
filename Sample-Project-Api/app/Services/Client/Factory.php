<?php

namespace App\Services\Client;

use Throwable;
use Exception;
use DB;
use Illuminate\Http\Response;
use Illuminate\Http\QueryException;
use App\Exceptions\DuplicateEntityException;
use App\Models\Feeding;
use Illuminate\Support\Facades\Auth;
use App\Models\CourierInformation;
use App\Models\SpecimenForm;
use Illuminate\Http\Request;

class Factory
{
    const SENT = 'Sent';
    private function throwException(Throwable $e)
    {
        if ($e instanceof QueryException && $e->errorInfo[1] == 1062) {
            throw new DuplicateEntityException($e->errorInfo[2], $e->getCode());
        }
    }
    public function feedingCreate($feedings, $specimenForm)
    {
        DB::beginTransaction();

        try {
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

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
            $this->throwException($e);
        }
    }

    public function feedingUpdate($feedings, $specimenForm)
    {
        DB::beginTransaction();

        try {
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
    }

    public function courierCreate($requestId, $validatedData)
    {
        DB::beginTransaction();

        try {
            if ($requestId !== Auth::id()) {
                return false;
            }
    
            $validatedData['result'] = self::SENT;
            $validatedData['user_id'] = Auth::id();
            $courierInformation = CourierInformation::create($validatedData);
            DB::commit();

            return $courierInformation;
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
            $this->throwException($e);
        }
    }

    public function checkStatusUpdate(Request $request)
    {
        DB::beginTransaction();

        try {
            $filteredData = $request->all(); 
           
            $checkedItems = array_filter($filteredData, function ($data) {
                return isset($data['checked']) && ($data['checked'] === true || $data['checked'] === 1);
            });

            if (count($checkedItems) === 0) {
                return false;
            } else {
                foreach ($filteredData as $data) {
                    $id = $data['id'];
                    $checked = $data['checked'];
            
                    SpecimenForm::where('id', $id)->update(['checked' => $checked]);
                }
    
                DB::commit();
                return true;
            }
        } catch(Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An error occurred while updating samples: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
