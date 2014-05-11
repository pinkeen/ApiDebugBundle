<?php

namespace Pinkeen\ApiDebugBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class CallController
{
    /**
     * Returns body of select api call's
     * request and response.
     *
     * @param string $id
     * @return Response
     */
    public function bodyAction($id)
    {
        try {
            $file = new File(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $id);
        } catch(FileNotFoundException $e) {
            throw new NotFoundHttpException("File '{$file->getRealpath()}' not found.");
        }

        if(!$file->isReadable()) {
            throw new NotFoundHttpException("Could not read '{$file->getRealpath()}'.");
        }

        return new Response(file_get_contents($file->getRealpath()), 200, [
            'Content-Type' => $file->getMimeType(),
        ]);        
    }
}
