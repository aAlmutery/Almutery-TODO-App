<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tasks;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CSVController extends Controller
{
    // Method to import CSV data
    public function import(Request $request)
    {
        try {

            $validation = Validator::make($request->all(),[
                'csv_file' => 'required|file|mimes:csv,txt',

              ]);
          
              if($validation->fails()){
                return response()->json([
                  'status' => 'failed',
                  'status_code' => 422,
                  'message' => 'Validation Error',
                  'Errors' => $validation->errors()
                ],422);
              }

            $file = $request->file('csv_file');
            $inputFileName = $file->getRealPath();

            // Load the CSV file
            $spreadsheet = IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            for ($i=0; $i < sizeof($data); $i++) { 
                if($i == 0 )
                    continue;


                if($data[$i][0] == null || $data[$i][0] == ''){
                    // create
                    Tasks::create([
                        "title" => $data[$i][1] == 'NULL' ? null : $data[$i][1],
                        "description" => $data[$i][2] == 'NULL' ? null : $data[$i][2],
                        "due_date" => $data[$i][3] == 'NULL' ? null : $data[$i][3],
                        "created_at" => now(),
                        "status" => $data[$i][6] == 'NULL' || $data[$i][6] == null || $data[$i][6] == ''  ? 1 : $data[$i][6],
                        "assign_to" => $data[$i][7] == 'NULL' ? null : $data[$i][7],
                        "parent" => $data[$i][8] == 'NULL' ? null : $data[$i][8],
                        "created_by" => $data[$i][9] == 'NULL' ? null : $data[$i][9],
                        "updated_by" => $data[$i][10] == 'NULL' ? null : $data[$i][10]
                    ]);
                }else{
                    
                    $check = Tasks::find($data[$i][0]);
                    // echo $data[$i][1] . '   <br> ------  ';
                    // echo $data[$i][2] . '   <br> ------  ';
                    // echo $data[$i][3] . '   <br> ------  ';
                    // echo $data[$i][4] . '   <br> ------  ';
                    // echo $data[$i][5] . '   <br> ------  ';
                    // echo $data[$i][6] . '   <br> ------  ';
                    // echo $data[$i][7] . '   <br> ------  ';
                    // echo $data[$i][8] . '   <br> ------  ';
                    // echo $data[$i][9] . '   <br> ------  ';
                    // echo $data[$i][10];
                    // dd($check->title);

                    if($check){
                        Tasks::where('id', $data[$i][0])->update([
                            "title" => $data[$i][1] == 'NULL' ? null : $data[$i][1],
                            "description" => $data[$i][2] == 'NULL' ? null : $data[$i][2],
                            "due_date" => $data[$i][3] == 'NULL' ? null : $data[$i][3],
                            "updated_at" => now(),
                            "status" => $data[$i][6] == 'NULL' || $data[$i][6] == null || $data[$i][6] == ''  ? 1 : $data[$i][6],
                            "assign_to" => $data[$i][7] == 'NULL' ? null : $data[$i][7],
                            "parent" => $data[$i][8] == 'NULL' ? null : $data[$i][8],
                            "created_by" => $data[$i][9] == 'NULL' ? null : $data[$i][9],
                            "updated_by" => $data[$i][10] == 'NULL' ? null : $data[$i][10]
                        ]);
                    }
                    
                }

            }

            return response()->json([
                'status' => 'success',
                'status_code' => 201,
                'message' => 'Task Imported Successfully',
            ],201);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => 'failed',
                'status_code' => 500,
                'message' => 'Somthing went Wrong with the server',
                'message2' => $th
            ],500);
        }
        
        // // If you want to redirect back with the data
        // return redirect()->back()->with('data', $data);
    }

    // Method to export data to CSV
    public function export()
    {
        
        $data = Tasks::all()->toArray();

        // Create a new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data, null, 'A1');

        // Create a CSV writer
        $writer = new Csv($spreadsheet);

        // Create a streamed response to output the CSV file
        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        // Set the response headers
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename="Tasks.csv"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
