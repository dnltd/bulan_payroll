<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;

class EmployeeApiController extends Controller
{
   
    public function index()
    {
        return response()->json([
            "status" => "success",
            "data" => Employee::all()
        ], 200);
    }

    public function show($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                "status" => "error",
                "message" => "Employee not found"
            ], 404);
        }

        return response()->json([
            "status" => "success",
            "data" => $employee
        ], 200);
    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        "first_name" => "required|string|max:255",
        "middle_name" => "nullable|string|max:255",
        "last_name" => "required|string|max:255",
        "email" => "required|email|max:255",
        "position" => "required|string|max:255",
        "contact_number" => "nullable|string|max:30",
        "address" => "nullable|string|max:255",
        "salary_rates_id" => "required|integer"
    ]);

    if ($validator->fails()) {
        return response()->json([
            "status" => "validation_error",
            "errors" => $validator->errors()
        ], 422);
    }

    $employee = Employee::create($request->all());

    // Convert to array to match JSON schema
    $employeeArray = $employee->toArray();

    return response()->json([
        "status" => "success",
        "message" => "Employee created successfully",
        "data" => $employeeArray
    ], 201);
}


    
    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                "status" => "error",
                "message" => "Employee not found"
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            "first_name" => "sometimes|required|string|max:255",
            "middle_name" => "sometimes|required|string|max:255",
            "last_name" => "sometimes|required|string|max:255",
            "position" => "sometimes|required|string|max:255",
            "contact_number" => "nullable|string|max:30",
            "address" => "nullable|string|max:255",
            "salary_rates_id" => "sometimes|required|integer"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "validation_error",
                "errors" => $validator->errors()
            ], 422);
        }

        $employee->update($request->all());

        return response()->json([
            "status" => "success",
            "message" => "Employee updated successfully",
            "data" => $employee
        ], 200);
    }

    //delete
    public function destroy($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                "status" => "error",
                "message" => "Employee not found"
            ], 404);
        }

        $employee->delete();

        return response()->json([
            "status" => "success",
            "message" => "Employee deleted successfully"
        ], 200);
    }
    public function boot()
    {
        Response::macro('xml', function ($data, $status = 200, $rootElement = 'response') {
            // data must be array
            if (!is_array($data)) {
                $data = (array) $data;
            }
            $xml = ArrayToXml::convert([$rootElement => $data], $rootElement);
            return response($xml, $status)->header('Content-Type', 'application/xml');
        });
    }
}
