<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkflowDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkflowController extends Controller
{
    public function index()
    {
        $workflows = WorkflowDefinition::with('creator')->get();
        return response()->json($workflows);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'form_schema' => 'required|array',
            'form_schema.fields' => 'required|array|min:1',
            'steps' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $workflow = WorkflowDefinition::create([
            'name' => $request->name,
            'description' => $request->description,
            'form_schema' => $request->form_schema,
            'steps' => $request->steps,
            'is_active' => true,
            'version' => 1,
            'created_by' => auth()->id(),
        ]);

        // Validate structure
        $errors = $workflow->validateStructure();
        if (!empty($errors)) {
            $workflow->delete();
            return response()->json(['errors' => $errors], 422);
        }

        return response()->json($workflow, 201);
    }

    public function update(Request $request, $id)
    {
        $workflow = WorkflowDefinition::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'form_schema' => 'array',
            'steps' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $workflow->update($request->only(['name', 'description', 'form_schema', 'steps']));

        return response()->json($workflow);
    }

    public function show($id)
    {
        $workflow = WorkflowDefinition::with('creator')->findOrFail($id);
        return response()->json($workflow);
    }

    public function activate($id)
    {
        $workflow = WorkflowDefinition::findOrFail($id);
        $workflow->update(['is_active' => true]);
        return response()->json($workflow);
    }

    public function deactivate($id)
    {
        $workflow = WorkflowDefinition::findOrFail($id);
        $workflow->update(['is_active' => false]);
        return response()->json($workflow);
    }

    public function destroy($id)
    {
        $workflow = WorkflowDefinition::findOrFail($id);
        
        // Check if workflow has requests
        if ($workflow->requests()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete workflow that has existing requests. Deactivate it instead.'
            ], 400);
        }

        $workflow->delete();
        return response()->json(['message' => 'Workflow deleted successfully']);
    }
}
