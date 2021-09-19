<?php

//  ** TODO ** 
//  ADD ROTATION CHARACTERISITCS

function parseCsvFile ( $fileName, $firstIsTitleRow = false ) {
    $toReturn= array();
    $titles  = array();
    echo "Opening $fileName\n";
    if ( ($fHandle = fopen( $fileName, 'r' )) !== false ) {
        if ( $firstIsTitleRow ) {
            $titles = fgetcsv($fHandle, ',');
            foreach ( $titles as $i => $v ) {
                $titles[$i] = strtolower( $v );
            }
        }
        while ( $thisLine = fgetcsv($fHandle, ',')) {
            $newLline = array();
            foreach ( $thisLine as $i => $v ) {
                if ( $firstIsTitleRow ) {
                    $newLine[ $titles[ $i ] ] = $v;
                } else {
                    $newLine[$i] = $v;
                }
            }
            $toReturn[] = $newLine;
        }
    }
    return $toReturn;
}

define ( 'SOURCEDIR' , '/gumstix-hardware/Production/PCB/');

$fileNameList = array ( 
                    array ( 'filename' => 'PCB30002-R2980/PCB30002.', 'sheet' => '2' ),
                    array ( 'filename' => 'PCB30002-R2980/PCB30002.', 'sheet' => '3' ),
                    array ( 'filename' => 'PCB30003-R2909/PCB30003.', 'sheet' => '2' )
                );                    

class component {
    protected $refDes;
    protected $xyLoc = array ( 'x' => 0, 'y' => 0 );
    protected $newDes;
    public function getPartType() {
        $pattern = '/^([A-Z]+[\$]*)([0-9]+)/';
        preg_match( $pattern, $this->refDes, $matches );
        return $matches[1];
    }
    public function getPartDes() {
        $pattern = '/^([A-Z]+[\$]*)([0-9]+)/';
        preg_match( $pattern, $this->refDes, $matches );
        return $matches[2];
    }
    public function setNewDes($theNewDes) {
        $this->newDes = $theNewDes;
    }
    public function getRefDes() {
        return $this->refDes;
    }
    public function getNewDes() {
        return $this->newDes;
    }
    public function getXyLoc() {
        return $this->xyLoc;
    }
    public function setXyLoc( $xyLocation, $yLocation ) {
        if (is_array( $xyLocation )) {
            if ( isset( $xyLocation['x'] )) {
                $this->xyLoc['x'] = $xyLocation['x'];
                $this->xyLoc['y'] = $xyLocation['y'];
            } else { 
                $this->xyLoc['x'] = $xyLocation['0'];
                $this->xyLoc['x'] = $xyLocation['1'];
            }
        } else {
            $this->xyLoc['x'] = $xyLocation;
            $this->xyLoc['y'] = $yLocation;
        }
    }
    public function __construct ( $toAdd ) {
        $this->refDes = $toAdd['component'];
    }
}

class sheet {
    protected $fileName;
    protected $sheetNumber;
    protected $componentList = array();

    public function getSheetFileName() {
        return $this->fileName;
    }
    public function getSheetNumber() {
        return $this->sheetNumber;
    }
    public function getComponentLocations ( $locationFileName ) {
        
    }
    public function issueSheet ( $newFileHandle, $newSchematicName ) {
        //  OPEN THE SOURCE SCHEMATIC
        //
        fprintf( $newFileHandle, "EDIT '%s';\n", SOURCEDIR . $this->fileName );
        fprintf( $newFileHandle, "EDIT '.S%s'; \n", $this->sheetNumber );
        //
        //  RENAME THE COMPONENTS AS NEEDED
        //
        foreach ( $this->componentList as $components ) {
            if ( $components->getNewDes() != '') {
                fprintf(  $newFileHandle, "NAME '%s' '%sXXX'\n", $components->getRefDes(), $components->getNewDes() );
            }
        }
        //
        //  COPY THE SHEET
        //
        fprintf( $newFileHandle, "GROUP ALL;\n" );
        fprintf( $newFileHandle, "CUT;\n"   );
        //
        //  OPEN THE TARGET SCHEMATIC
        //
        fprintf( $newFileHandle, "EDIT '%s'; \n", $newSchematicName );
        fprintf( $newFileHandle, "EDIT '.S99';\n" );
        fprintf( $newFileHandle, "PASTE ( 0 0 );\n" );
        fprintf( $newFileHandle, "WRITE;\n" );
        //
        //  EDIT THE TARGET BOARD AND POSITION COMPONENTS
        //
        fprintf( $newFileHandle, "EDIT '%s'\n", str_replace( 'sch', 'brd', $newSchematicName ) );
        fprintf( $newFileHandle, "GRID MM;\n" );
        foreach ( $this->componentList as $components ) {
            $loca = $components->getXyLoc();
            if ( $components->getNewDes() != '' ) {
                fprintf(  $newFileHandle, "NAME '%sXXX' '%s';\n", $components->getNewDes(), $components->getNewDes() );
                fprintf(  $newFileHandle, "MOVE '%s' ( %s  %s );\n", $components->getNewDes(), $loca['x'], $loca['y'] );
            } else {
                fprintf(  $newFileHandle, "MOVE '%s' ( %s  %s );\n", $components->getRefDes(), $loca['x'], $loca['y'] );
            }
        }
        fprintf( $newFileHandle, "WRITE;\n" );
    }
    public function listComponents() {
        return $this->componentList;
    }
    public function __construct ( $fileSheetName ) {
        $this->fileName    = $fileSheetName['filename'] . 'sch';
        $this->sheetNumber = $fileSheetName['sheet'];
        $theSource = parseCsvFile ( SOURCEDIR . $fileSheetName['filename'] . 'sch.mod.csv', true );
        foreach ( $theSource as $componentToAdd ) {
            if ( $componentToAdd['sheet'] ==  $this->sheetNumber ) {
                $nextComponent = new component( $componentToAdd );
                $this->componentList[ $nextComponent->getRefDes() ] = $nextComponent;
            }
        }
        $allLocations = parseCsvFile( SOURCEDIR . $fileSheetName['filename'] . 'XY.csv', false  );
        echo SOURCEDIR . $fileSheetName['filename'] . 'XY.csv' . "\n";
        foreach ( $allLocations as $locToFix ) {
            if ( is_object( $this->componentList[ $locToFix[0]  ])) {
                $this->componentList[ $locToFix[0]  ]->setXyLoc( $locToFix['1'], $locToFix['2'] );
            }
        }
    }
}

class sheetList {
    protected $allComponentRefDes = array();
    protected $highestValue = array ();
    protected $sheets  = array ();

    public function updateHighestValues( $theSheet ) {
        $theComponentList = $theSheet->listComponents();
        foreach ( $theComponentList as $theComponent ) {
            $partType = $theComponent->getPartType();
            $this->highestValue[ $partType ] = max ( $this->highestValue[ $partType ], $theComponent->getPartDes() );
        }
    }
    public function resetRefDes( $theSheet ) {
        $theComponentList = $theSheet->listComponents();
        foreach ( $theComponentList as $theComponent ) {
            $partType = $theComponent->getPartType();
            if ( $this->allComponentRefDes[ $theComponent->getRefDes() ] ) {
                $this->highestValue[ $partType ] ++;
                $newDes = $partType . $this->highestValue[ $partType ];
                $theComponent->setNewDes( $newDes );
                $this->allComponentRefDes[ $newDes ] = true;                
            } else {
                $this->allComponentRefDes[ $theComponent->getRefDes() ] = true;
            }
        }
    }
    public function issueSheetList ( $newFileHandle, $newSchematicName ) {
        foreach ( $this->sheets as $thisSheet ) {
            echo 'Printing ' . $thisSheet->getSheetFileName() . '.S' . $thisSheet->getSheetNumber() . "\n";
            $thisSheet->issueSheet( $newFileHandle, $newSchematicName );
        }
    }
    public function __construct ( $fileNameList ) {
        foreach ( $fileNameList as $fileSheetName ) {
            $newSheet = new sheet( $fileSheetName );
            $this->updateHighestValues( $newSheet );
            $this->resetRefDes( $newSheet );
            $this->sheets[] = $newSheet;
        }
    }
}

$ulpFileHandle = fopen( "newfileB.scr", 'w' );
$newSchematicFileName = "/jsConf/B50000.sch";
$theFiles   = new sheetList( $fileNameList );
$theFiles->issueSheetList( $ulpFileHandle,  $newSchematicFileName );
fclose ( $ulpFileHandle );

?>