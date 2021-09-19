function TableData(headers, row, loc)
{
    this.headerArr = headers;
    this.rowArr = row;
    this.myLoc = loc;
    {
    loc.document.close();
    loc.document.open();
    loc.document.write('<table id = "copyTable" border = "1">');
    for(i=0; i<this.headerArr.length; i++)
	{   
	    loc.document.write('<tr><th>');
	    loc.document.write(this.headerArr[i]);
	    loc.document.write('</th><td class = "tan">');
	    loc.document.write(this.rowArr[i]);
	    loc.document.write('</td></tr>');
	}
    loc.document.write('</table>');
    }

}

function te_mouseOver(id)
{
    var row = id;
    row.style.backgroundColor = "#FFFFCF";
}

function te_mouseOut(id)
{
    var row = id; 
    if ( parent.document.highlighted_row != id) 
	{
	row.style.backgroundColor= "#F3FFFF";
	}
}

function  addRow(numRows)
{ 
    var table = document.getElementById('ticket_list').insertRow(-1);
    table.setAttribute('onmouseover', 'te_mouseOver(this)');
    table.setAttribute('onmouseout', 'te_mouseOut(this)');
    for(i=0; i<=numRows; i++)
	{
	    var x = table.insertCell(i);
	    x.innerHTML = '___';
	    x.setAttribute('onclick', 'changeRow(this)');
	}
}

function view_ticket(id)
{
    var headers = parent.document.getElementById("ticket_headers");
    var row = parent.document.highlighted_row;
    if ( row ) 
	{
	row.style.backgroundColor = "#F3FFFF";
	}
    parent.document.highlighted_row = id;
    for ( i=0; i<4; i++ )
    {
        var headerName = headers.rows[0].cells[i].innerHTML;
        var cellData = parent.document.getElementById(  headerName ) ;
        cellData.innerHTML = id.cells[i].innerHTML;
    }
}

function te_sortTable(colNum, numRows)
{
    var originalData = new Array();
    var dataArr = new Array();
    for(i=0; i<numRows; i++)
    {
	originalData[i] = parent.document.getElementById('te_row_' + i).innerHTML;
	dataArr[i] = parent.document.getElementById('te_row_' + i ).innerHTML;
    }
    
    for(i=0; i<numRows; i++)
    {
       	var z = dataArr[i].indexOf(">");
	dataArr[i] = dataArr[i].slice(z+1);
	dataArr[i] = dataArr[i].split("</td><td>" || "</td>" || "<td>");
	var len = dataArr[i].length;
	var y = dataArr[i][len-1].indexOf("<");
	dataArr[i][len-1] = dataArr[i][len-1].slice(0,y);
    }
    
    var toSort = new Array();
    for(i=0; i<numRows; i++)
    {
	toSort[i] = dataArr[i][colNum];
    }
    
    var type = "empty";
    
    for(var i=0; i<toSort.length; i++)
    {
	    if(isNumeric(toSort[i]) && (type == "empty" || type == "num"))
		{
		    type = "num";
		}
	    else if(isDate(toSort[i]) && (type == "empty" || type == "date"))
	        {
		    type = "date";
		}
	    else 
		{
		    type = "string";
		    break;
		}
	}
    
    if(type == "num")
	toSort.sort(numSort);
    else if(type == "date")
	toSort.sort(dateSort);
    else
	toSort.sort();
    
    reprint(toSort, dataArr, originalData, colNum, numRows);
}

function reprint(sorted, allData, innerH, colNum, numRows)
{
    for(i = 0; i<numRows; i++)
	{
	    for(j=0; j < allData.length; j++)
		{
		    if(sorted[i] == allData[j][colNum])
			{
			    parent.document.getElementById('te_row_' + i).innerHTML = innerH[j];
			    allData[j].splice(colNum, colNum);
			    break;
			}
		}
	}
}

function dateSort(a,b)
{
    var z = a.lastIndexOf("/");
    var y = b.lastIndexOf("/");
    
    var aYear = a.slice(z+1);
    var bYear = b.slice(y+1);

    if(aYear != bYear)
	{
	    return aYear - bYear;
	}
    else
	{
	    w = a.indexOf("/");
	    x = b.indexOf("/");
	    var aMonth = a.slice(w+1, z);
	    var bMonth = b.slice(x+1, y);
	    
	    if(aMonth.indexOf(0) == 0)
		{
		   aMonth = aMonth.slice(1);
		}
	    if(bMonth.indexOf(0) == 0)
		{
		    bMonth = bMonth.slice(1);
		}

	    if(aMonth != bMonth)
		{
		    return aMonth - bMonth;
		}
	    else
		{
		    var aDay = a.slice(0, w);
		    var bDay = b.slice(0, x);
		    return aDay - bDay;
		}
	}
}
    
function numSort(a,b)
{
    if(a.length > b.length)
	return -1;
    if(a.length < b.length)
	return 1;
    if(a.length == b.length)
	{
	    for(i=0; i<a.length; i++)
		{
		    if(a[i]!=b[i])
			{
			    if(a[i] > b[i])
				return -1;
			    else
				return 1;
			    break;
			}
		}
	}
    
}

function isNumeric(num)
{
    var ValidChars = "0123456789,";
    var IsNumber=true;
    var Char;

 
    for (i = 0; i < num.length && IsNumber == true; i++) 
	{ 
	    Char = num.charAt(i); 
	    if (ValidChars.indexOf(Char) == -1) 
		{
		    IsNumber = false;
		}
	}
    return IsNumber;
   
}

function isDate(date)
{
    var z = date.indexOf("/");
    if(z ==1 || z ==2)
	{
	    var mmyy = date.slice(z+1);
	    var y = mmyy.indexOf("/");
	    if(y==1 || y==2)
		{
		    return true;
		} 
	}
    else
	{
	    return false;
	}
}
    
