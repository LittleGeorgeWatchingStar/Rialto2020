SELECT parent.WORef AS ParentID, parent.Closed, parent.StockID, parent.ReleasedDate, child.WORef AS ChildID, child.ParentBuild, child.Closed, child.StockID, child.ReleasedDate 
FROM WorksOrders parent 
INNER JOIN WorksOrders child 
ON parent.WORef = child.ParentBuild 
WHERE parent.Closed = '0' AND child.Closed = '1';

UPDATE WorksOrders parent 
INNER JOIN WorksOrders child  
ON parent.WORef = child.ParentBuild 
SET parent.Closed = '1' 
WHERE parent.Closed = '0' AND child.Closed = '1';

SELECT WORef, UnitsReqd, UnitsRecd, Closed, ReleasedDate, StockID 
FROM WorksOrders 
WHERE UnitsRecd >= UnitsReqd AND Closed = '0';
