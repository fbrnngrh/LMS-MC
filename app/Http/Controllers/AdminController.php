<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AdminController extends Controller
{
    public function AdminDashboard()
    {
        return view('admin.index');
    }

    public function AdminLogout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    public function AdminLogin()
    {
        return view('admin.admin_login');
    }

    public function AdminProfile()
    {

        $id = Auth::user()->id;

        $profileData = User::find($id);

        return view('admin.admin_profile_view', compact('profileData'));
    }

    public function AdminProfileStore(Request $request)
    {
        $id = Auth::user()->id;
    
        $profileData = User::find($id);
    
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $id,
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        // Redirect back with errors if validation fails
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        // Update profile data
        $profileData->name = $request->name;
        $profileData->username = $request->username;
        $profileData->email = $request->email;
        $profileData->phone = $request->phone;
        $profileData->address = $request->address;
    
        // Process and save the new photo
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            @unlink(public_path('upload/admin_images/' . $profileData->photo));
            $imageName = date('YmdHi') . '-' . $image->getClientOriginalName();
            $image->move(public_path('upload/admin_images'), $imageName);
            $profileData->photo = $imageName;
        }
    
        $profileData->save();

        $notification = array(
            'message' => 'Admin Profile Updated Successfully',
            'alert-type' => 'success'
        );
    
        return redirect()->back()->with($notification);
    }

    public function AdminChangePassword()
    {
        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('admin.admin_change_password',compact('profileData'));
    }

    public function AdminPasswordUpdate(Request $request)
    {
    
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);
    
        // Redirect back with errors if validation fails
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        // Check if old password matches
        if (!Hash::check($request->old_password, auth::user()->password)) {
            $notification = array(
                'message' => 'Old Password Does Not Match',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        }
    
        // Update password 
        User::whereId(auth::user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

    
        $notification = array(
            'message' => 'Password Change Successfully',
            'alert-type' => 'success'
        );
    
        return redirect()->back()->with($notification);
    }

    public function BecomeInstructor()
    {
        return view('frontend.instructor.reg_instructor');
    }

    public function InstructorRegister(Request $request)
    {
        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required', 'string','unique:users'],
        ]);

        User::insert([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' =>  Hash::make($request->password),
            'role' => 'instructor',
            'status' => '0',
        ]);

        $notification = array(
            'message' => 'Instructor Registed Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('instructor.login')->with($notification); 
    }

    public function AllInstructor()
    {
        $allinstructor = User::where('role','instructor')->latest()->get();
        return view('admin.backend.instructor.all_instructor',compact('allinstructor'));
    }

    public function UpdateUserStatus(Request $request)
    {
        $userId = $request->input('user_id');
        $isChecked = $request->input('is_checked',0);

        $user = User::find($userId);
        if ($user) {
            $user->status = $isChecked;
            $user->save();
        }
        return response()->json(['message' => 'User Status Updated Successfully']);
    }// End Method

}
