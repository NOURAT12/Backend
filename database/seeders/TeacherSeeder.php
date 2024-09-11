<?php

namespace Database\Seeders;
use App\Models\Level;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $user=User::create([
            'name' =>'teacher',
            'username' => 'teacher',
            'active' => 1,
            'otp' => null,
            'password' => Crypt::encryptString('12345678'),
        ]);
        $teacher=Teacher::create([
            'gender'=>'m',
            'phone'=>'0972638483',
            'birthday'=>null,
            'f_name'=>'nour',
            'l_name'=>'aldeen',
            'address_id'=>1,
            'user_id'=>$user->id
        ]);
        $role = Role::where('name', 'teacher')->first();
        $user->addRole($role);
        $student1=User::create([
            'name' =>'ahmad',
            'username' => 'ahmad',
            'active' => 1,
            'otp' => null,
            'password' => Crypt::encryptString('12345678'),
        ]);
        $role = Role::where('name', 'student')->first();
        $student1->addRole($role);
        $student2=User::create([
            'name' =>'nour',
            'username' => 'nour',
            'active' => 1,
            'otp' => null,
            'password' => Crypt::encryptString('12345678'),
        ]);
        $role = Role::where('name', 'student')->first();
        $student2->addRole($role);
        $student3=User::create([
            'name' =>'mo',
            'username' => 'mo',
            'active' => 1,
            'otp' => null,
            'password' => Crypt::encryptString('12345678'),
        ]);
        $role = Role::where('name', 'student')->first();
        $student3->addRole($role);
        $student4=User::create([
            'name' =>'khaled',
            'username' => 'khaled',
            'active' => 1,
            'otp' => null,
            'password' => Crypt::encryptString('12345678'),
        ]);
        $role = Role::where('name', 'student')->first();
        $student4->addRole($role);
        $student5=User::create([
            'name' =>'omar',
            'username' => 'omar',
            'active' => 1,
            'otp' => null,
            'password' => Crypt::encryptString('12345678'),
        ]);
        $role = Role::where('name', 'student')->first();
        $student5->addRole($role);
        $student6=User::create([
            'name' =>'mazen',
            'username' => 'mazen',
            'active' => 1,
            'otp' => null,
            'password' => Crypt::encryptString('12345678'),
        ]);
        $role = Role::where('name', 'student')->first();
        $student6->addRole($role);
        $student7=User::create([
            'name' =>'yaser',
            'username' => 'yaser',
            'active' => 1,
            'otp' => null,
            'password' => Crypt::encryptString('12345678'),
        ]);
        $role = Role::where('name', 'student')->first();
        $student7->addRole($role);
        $student8=User::create([
            'name' =>'salom',
            'username' => 'salom',
            'active' => 1,
            'otp' => null,
            'password' => Crypt::encryptString('12345678'),
        ]);
        $role = Role::where('name', 'student')->first();
        $student8->addRole($role);
        $student9=User::create([
            'name' =>'abeer',
            'username' => 'abeer',
            'active' => 1,
            'otp' => null,
            'password' => Crypt::encryptString('12345678'),
        ]);
        $role = Role::where('name', 'student')->first();
        $student9->addRole($role);
        $student12=Student::create([
            'gender'=>'m',
            'phone'=>'0972638483',
            'birthday'=>'null',
            'f_name'=>$student1->name,
            'l_name'=>'aldeen',
            'address_id'=>1,
            'user_id'=>$student1->id,
            'parent_phone'=>'9837461738',
            'mother_name'=>'krnba',
            'father_name'=>'gdba',
            'full_name'=>'bbdd'
        ]);
        $student13=Student::create([
            'gender'=>'m',
            'phone'=>'0972638483',
            'birthday'=>'null',
            'f_name'=>$student2->name,
            'l_name'=>'aldeen',
            'address_id'=>1,
            'user_id'=>$student2->id,
            'parent_phone'=>'9837461738',
            'mother_name'=>'krnba',
            'father_name'=>'gdba',
            'full_name'=>'bbdd'
        ]);
        $student14=Student::create([
            'gender'=>'m',
            'phone'=>'0972638483',
            'birthday'=>'null',
            'f_name'=>$student3->name,
            'l_name'=>'aldeen',
            'address_id'=>1,
            'user_id'=>$student3->id,
            'parent_phone'=>'9837461738',
            'mother_name'=>'krnba',
            'father_name'=>'gdba',
            'full_name'=>'bbdd'
        ]);
        $student15=Student::create([
            'gender'=>'m',
            'phone'=>'0972638483',
            'birthday'=>'null',
            'f_name'=>$student4->name,
            'l_name'=>'aldeen',
            'address_id'=>1,
            'user_id'=>$student4->id,
            'parent_phone'=>'9837461738',
            'mother_name'=>'krnba',
            'father_name'=>'gdba',
            'full_name'=>'bbdd'
        ]);
        $student16=Student::create([
            'gender'=>'m',
            'phone'=>'0972638483',
            'birthday'=>'null',
            'f_name'=>$student5->name,
            'l_name'=>'aldeen',
            'address_id'=>1,
            'user_id'=>$student5->id,
            'parent_phone'=>'9837461738',
            'mother_name'=>'krnba',
            'father_name'=>'gdba',
            'full_name'=>'bbdd'
        ]);
        $student17=Student::create([
            'gender'=>'m',
            'phone'=>'0972638483',
            'birthday'=>'null',
            'f_name'=>$student6->name,
            'l_name'=>'aldeen',
            'address_id'=>1,
            'user_id'=>$student6->id,
            'parent_phone'=>'9837461738',
            'mother_name'=>'krnba',
            'father_name'=>'gdba',
            'full_name'=>'bbdd'
        ]);
        $student18=Student::create([
            'gender'=>'m',
            'phone'=>'0972638483',
            'birthday'=>'null',
            'f_name'=>$student7->name,
            'l_name'=>'aldeen',
            'address_id'=>1,
            'user_id'=>$student7->id,
            'parent_phone'=>'9837461738',
            'mother_name'=>'krnba',
            'father_name'=>'gdba',
            'full_name'=>'bbdd'
        ]);
        $student19=Student::create([
            'gender'=>'m',
            'phone'=>'0972638483',
            'birthday'=>'null',
            'f_name'=>$student8->name,
            'l_name'=>'aldeen',
            'address_id'=>1,
            'user_id'=>$student8->id,
            'parent_phone'=>'9837461738',
            'mother_name'=>'krnba',
            'father_name'=>'gdba',
            'full_name'=>'bbdd'
        ]);
        $student20=Student::create([
            'gender'=>'m',
            'phone'=>'0972638483',
            'birthday'=>'null',
            'f_name'=>$student9->name,
            'l_name'=>'aldeen',
            'address_id'=>1,
            'user_id'=>$student9->id,
            'parent_phone'=>'9837461738',
            'mother_name'=>'krnba',
            'father_name'=>'gdba',
            'full_name'=>'bbdd'
        ]);


    }
}
