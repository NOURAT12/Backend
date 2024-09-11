<?php
namespace App\Traits;

Trait ImageTrait{


    private function setLogo($request,$subpath)
    {
        $image = $request->file('logo');
        $fileName = uniqid().'.'.$image->getClientOriginalExtension();
        $path = 'images/' .$subpath.'/'. $fileName;
        $image->move(public_path('images/'.$subpath), $fileName);
        return $path;
    }
    private function getLogo($image)
    {
        if (file_exists($image)) {
            return   asset('/'.$image);
        } else {
            return null;
        }

    }
    private function setImage($request)
    {
        $image = $request->file('logo');
        $fileName = uniqid().'.'.$image->getClientOriginalExtension();
        $path = 'images/' . $fileName;
        $image->move(public_path('images'), $fileName);
        return $path;
    }
    private function setFile($image,$subpath)
    {
        $fileName = uniqid().'.'.$image->getClientOriginalExtension();
        $path = 'resources/' .$subpath.'/'. $fileName;
        $image->move(public_path('resources/'.$subpath), $fileName);
        return $path;
    }


    private function getImage( $image)
    {
        if (file_exists($image)) {
            // return "image";
            return   asset('/'.$image);
        } else {
            return null;
        }

    }


    private function setSubjectLogo($request,$subpath)
    {
        $image = $request;
        $fileName = uniqid().'.'.$image->getClientOriginalExtension();
        $path = 'images/' .$subpath.'/'. $fileName;
        $image->move(public_path('images/'.$subpath), $fileName);
        return $path;
    }

}
