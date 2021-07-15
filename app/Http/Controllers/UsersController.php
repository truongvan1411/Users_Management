<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Unit;
use Hash;
use DB;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = DB::table('users')
                        ->where('admin', '=', 0)
                        ->orderBy('created_at', 'DESC')
                        ->paginate(100);
        if(Auth::user()->admin) {
            return view('users.index', compact('users'));
        }
        else {
            return view('/profile.index', compact('users'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Auth::user()->admin) {
            $current_user = Auth::user();
            $units = DB::table('units')->select('unit_name')->get();
            $unit_name = array();
            
            foreach ($units as $unit) {
                $unit_name[] = $unit->unit_name;
            }
            return view('users.create', compact('current_user', 'unit_name'));
        }
        else {
            return redirect('/home');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Auth::user()->admin) {

            $validatedData = $request->validate([
                'name' => 'required|max:255|min:3',
                'email' => 'required|unique:users|max:190',
                'password' => 'required|min:8',
                // 'unit' => 'required|max:40',
                'address' => 'required|max:255',
                // 'create_by' => 'required|min:3|max:255'
            ]);
            
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->birth_date = $request->birth_date;
            // $user->gender = $request->gender; 
            
            $user->unit = $request->unit;
            $user->description = $request->description;
            $user->address = $request->address;
            $user->password = Hash::make($request->password);
            $user->admin = '0';
            $user->created_by = $request->created_by;
           
            $user->save();

            // $units = Unit::select('id')->where('unit_name',$request->unit)->get();
            // $id_unit = $units->id;
            $unit = Unit::find($request->unit);
            $unit->user_id = $user->email;

            $unit->save();

            return redirect('/admin-panel')->with('msg_success', 'User Created Successfully');
            
        }
        else {
            return redirect('/home');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        if(Auth::user()->admin) {
            return view('users.view', compact('user'));
        }
        else {
            return view('profile.view', compact('user'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(Auth::user()->admin) {
            $user = User::findOrFail($id);
            $current_user = Auth::user();
            $units = DB::table('units')->select('unit_name')->get();
            $unit_name = array();
            foreach ($units as $unit) {
                $unit_name[] = $unit->unit_name;
            }
            return view('users.edit', compact('user', 'current_user', 'unit_name'));


        }
        else {
            return redirect('/home');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(Auth::user()->admin) {

            $validatedData = $request->validate([
                'name' => 'required|min:3|max:255',
                'email' => 'required|max:190',
                // 'password' => 'nullable|min:8|confirmed',
                'unit' => 'required|max:40',
                'address' => 'required|max:255',
                // 'update' => 'required|min:3|max:255'
            ]);

            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->birth_date = $request->birth_date;
            $user->description = $request->description;
            $user->unit = $request->unit;
            $user->address = $request->address;
            $user->updated_by = $request->updated_by;
           
            $user->save();

            return redirect('/admin-panel')->with('msg_success', 'User Updated Successfully');
        }
        else {
            return redirect('/home');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Auth::user()->admin) {
            $user = User::find($id);
            $user->delete();
            return redirect('/admin-panel')->with('msg_success', 'User Deleted Successfully');
        }
        else {
            return redirect('/home');
        }
    }

    // public function search()
    // {
    //     return view('users.search');
    // }

    public function search(Request $request)
    {
        
        if ($request->search != '') {
            if ($request->ontype == 'name') {
                $data = User::where('name','like','%'.$request->search.'%')->where('admin','=','0')->get();
                $ontype = "Name";
            }
            else if ($request->ontype == 'description') {
                $data = User::where('description','like','%'.$request->search.'%')->where('admin','=','0')->get();
                $ontype = "Description";
            }
            else if ($request->ontype == 'address') {
                $data = User::where('address','like','%'.$request->search.'%')->where('admin','=','0')->get();
                $ontype = "Address";
            }
        }
            
        $key = $request->search;
        if(Auth::user()->admin) {
            return view('users.search', compact('data', 'key', 'ontype'));
        }
        else {
            return view('profile.search', compact('data', 'key', 'ontype'));
        }
        
    }
}
