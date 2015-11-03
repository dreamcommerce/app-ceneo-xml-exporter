<?php


namespace CeneoBundle\Services;


class FileCompressor
{
    protected function getCompressorCmdLine($path, $target = null){
        $path = realpath($path);

        if(!$target){
            $target = $path.'.gz';
        }

        $cmd = [
            escapeshellcmd('gzip'),
        ];

        foreach(['-k', '-c', $path] as $c){
            $cmd[] = escapeshellarg($c);
        }

        $cmd[] = '>';
        $cmd[] = escapeshellarg($target);

        return $cmd;
    }

    public function compressAsync($path, $target = null)
    {
        $compressor = $this->getCompressorCmdLine($path, $target);
        $compressor[] = ' &';
        shell_exec(implode(' ', $compressor));
    }

    public function compress($path, $target = null)
    {
        $compressor = $this->getCompressorCmdLine($path, $target);
        shell_exec(implode(' ', $compressor));
    }
}