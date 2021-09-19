dojo.require("dojo.data.ObjectStore");
dojo.require("dojo.store.JsonRest");
dojo.require("dojox.grid.DataGrid");
dojo.require("dijit.form.Button");




function TestPage()
{
    this.init = function()
    {
        this.initDatastore();
        this.initGrid();
        this.initForm();
    };

    this.initDatastore = function()
    {
        this.customers = new dojo.store.JsonRest({
            target: "/rest.php/customers/Customer"
        });
    };

    this.initForm = function()
    {
        var searchBtn = new dijit.form.Button({
            onClick: function()
            {
                var txt = dojo.byId('searchTxt');
                var searchTerm = txt.value;
                this.grid.setQuery({matching: searchTerm})
            }
        }, 'searchBtn');
        searchBtn.grid = this.grid;
        searchBtn.startup();
    };

    this.initGrid = function()
    {
        this.grid = new dojox.grid.DataGrid({
            store: dojo.data.ObjectStore({objectStore: this.customers}),
            query: { matching: "Ian Phillips" },
            structure: [
                { name: "Name", field: "Name" },
                { name: "Company", field: "CompanyName"}
            ]
        }, "grid");

        this.grid.startup();
    };
}

dojo.ready(function() {
    var page = new TestPage();
    page.init();
});

