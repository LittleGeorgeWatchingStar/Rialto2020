<?php
		
class Display_Table
{
      public $data = array();
      public $columns = array();      
      public $numRows = 0;

      function Display_Table($columnHeaders)
      {     
	foreach($columnHeaders as $columnName => $columnOrder)
	{
		$this->columns[$columnName]= $columnOrder;
	}
      }

      function printHeaderCol()
      {
	foreach($this->columns as $columnName => $columnOrder)
	{
		echo $columnName." ".$columnOrder."<br>";
	}
      }

      function getData()
      {
	return $data;
      }

      function orderHeaderColumn()
      {
	asort($this->columns);
      }

      function getColumnHeader()
      {
	return $this->columns;
      }

      function getNumRows()
      {
	return $this->numRows;
      }

      function addRow($rowToAdd)
      {
	$columnHeaders = array_keys($this->columns);
	$correctRow = array();
	foreach($columnHeaders as $value)
	{
		$correctRow[$value] = $rowToAdd[$value];
	}
	foreach($correctRow as $header => $value)
	{
		$this->data[$this->numRows][$header] = $value;		
	}
	$this->numRows++;      
      }

      function printJSTable()
      {
        echo '<script src = "Test/ticket_explorer.js"></script>';
	echo '<table id = "ticket_list" class = "ticket_explorer" border = "1">';
        echo '<thead id = "ticket_headers"><tr>';
	$counter = 0;
        foreach($this->columns as $header => $order)
        {
                echo '<th onclick = "te_sortTable('.$counter.','.$this->numRows.')">';
                echo $header;
                echo '</th>';
		$counter++;
        }
        echo '</tr></thead><tbody>';
	$counter = 0;
        for($i = 0; $i < $this->numRows; $i++)
        {
		echo '<tr id = "te_row_'.$i.'" onclick = "view_ticket(this)" onmouseover = "te_mouseOver(this)" onmouseout = "te_mouseOut(this)">';
		foreach($this->data[$i] as $key => $value)
                {
                        echo '<td>';
                        if($value!="")
                                echo $value;
                        else
                        {
                                echo '';
                        }
                        echo '</td>';

                }
                echo '</tr>';
        }
        echo '</tbody></table>';
	echo '<BR>';
	include("ticket_view.html");
	}

};

?>
