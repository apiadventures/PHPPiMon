<?php

require_once('Thread.php');

class FileAlterationMonitor
{
    private $scanFolder, $initialFoundFiles;

    public function __construct($scanFolder)
    {
        $this->scanFolder = $scanFolder;
        $this->updateMonitor();
    }

    private function _arrayValuesRecursive($array)
    {
        $arrayValues = array();

        foreach ($array as $value)
        {
            if (is_scalar($value) OR is_resource($value))
            {
                 $arrayValues[] = $value;
            }
            elseif (is_array($value))
            {
                 $arrayValues = array_merge( $arrayValues, $this->_arrayValuesRecursive($value));
            }
        }

        return $arrayValues;
    }

    private function _scanDirRecursive($directory)
    {
        $folderContents = array();
        $directory = realpath($directory).DIRECTORY_SEPARATOR;

        foreach (scandir($directory) as $folderItem)
        {
            if ($folderItem != "." AND $folderItem != "..")
            {
                if (is_dir($directory.$folderItem.DIRECTORY_SEPARATOR))
                {
                    $folderContents[$folderItem] = $this->_scanDirRecursive( $directory.$folderItem."\\");
                }
                else
                {
                    $folderContents[] = $folderItem;
                }
            }
        }

        return $folderContents;
    }

    public function getNewFiles()
    {
        $finalFoundFiles = $this->_arrayValuesRecursive( $this->_scanDirRecursive($this->scanFolder));

        if ($this->initialFoundFiles != $finalFoundFiles)
        {
            $newFiles = array_diff($finalFoundFiles, $this->initialFoundFiles);
            return empty($newFiles) ? FALSE : $newFiles;
        }
    }

    public function getRemovedFiles()
    {
        $finalFoundFiles = $this->_arrayValuesRecursive( $this->_scanDirRecursive($this->scanFolder));

        if ($this->initialFoundFiles != $finalFoundFiles)
        {
            $removedFiles = array_diff( $this->initialFoundFiles, $finalFoundFiles);
            return empty($removedFiles) ? FALSE : $removedFiles;
        }
    }

    public function updateMonitor()
    {
        $this->initialFoundFiles = $this->_arrayValuesRecursive($this->_scanDirRecursive( $this->scanFolder));
    }
}

// function to be ran on separate threads
function paralel( $_name, $nFile ) {
        echo 'Now running thread ' . $_name . PHP_EOL;
        //sleep(rand(3,10));
        $output = exec("/home/pi/Dropbox-Uploader/dropbox_uploader.sh upload /tmp/motion/$nFile");
        $path = "/tmp/motion/" . $nFile ;
        print "\nThe Path is ".$path."\n";


        $result = unlink('/tmp/motion/'.$nFile);
        if ($result == 1)
            echo "File Deleted ". $nFile;

}

$f = new FileAlterationMonitor("/tmp/motion");

while (TRUE)
{
    if ($newFiles = $f->getNewFiles())
    {
    print("Yay, found a new file ...\n");
    // create 2 thread objects
    
	
	foreach( $newFiles as $newFile ):
	
        $t1 = new Thread( 'paralel', $newFile );
        $t1->start( 't1' );

		sleep(1);
	endforeach;


    }

    if ($removedFiles = $f->getRemovedFiles())
    {
		foreach( $removedFiles as $newFile ):
			print "\nDeleted ... " . "/tmp/motion/$newFile" . " \n";
		endforeach;
    }

    $f->updateMonitor();
}
?>
