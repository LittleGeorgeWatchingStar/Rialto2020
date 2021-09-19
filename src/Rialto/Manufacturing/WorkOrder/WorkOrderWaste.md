To "waste" a work order is to write off any issued stock as a loss.  Wasting
is analogous to receiving a work order, with the following differences:

 * no stock is created

 * the value of issued stock, which is currently in the Work In Process (WIP)
   account, is moved to Material Cost instead of Finished Inventory.

For all examples below, let's imagine we've ordered a build of 200 units of
some product. Let's also imagine that we've already issued the first 100,
which moves, say, $100 from the Raw Inventory account to WIP.


Example 1
---------

Now, for whatever reason, we learn that we are not going to receive those
100 units -- it's a bad build and they are going in the trash. However, since the
other 100 units have not been issued, we can reclaim those parts and use
them for some other purpose. In accounting terms, the other $100 worth of stock
is still in Raw Inventory.

So we go to the receiving page and check the "waste" checkbox and enter 100
into the quantity input -- 100 because that's the quantity that is already
issued and going in the trash.

In this case, the $100 in WIP moves to Material Cost.


Example 2
---------
In this example, let's imagine that the entire 200 will go to waste, even
though we've only issued the first 100.

So we go to the receiving page and check the "waste" checkbox and enter 200
into the quantity input -- 200 because we're throwing the whole build away.
In this case, here's what happens:

* The unissued 100 units are issued. Now all 200 units are issued and $200
  in total is in WIP. All of the components have now been consumed.

* The entire $200 in WIP gets moved to Material Cost.

* No new stock is created.


Example 3
---------
In this example, let's imagine that we have a parent build for 200 units, in
addition to the child build described above. In this example, none of the
parent units have been issued yet.

Let's say we want to waste the 100 issued child units, but reallocate all 200
parent units to other uses. Here's what we do:

Go to the receiving page, check the "waste" box, and *uncheck* the "receive
parent" box -- we're saying that we are going to waste the child but *not* the
parent. When we submit, here's what happens:

* The $100 worth of child stock in WIP moves to Material Cost.

* The allocations that the parent had on the child are released, since the
  parent now cannot use the first 100 child units for itself.