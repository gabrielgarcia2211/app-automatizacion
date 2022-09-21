<template>
  <div class="container-fluid">
    <ag-grid-vue
      style="width: 100%; height: 400px"
      class="ag-theme-material"
      :columnDefs="columnDefs"
      :defaultColDef="defaultColDef"
      :rowData="rowData"
      :rowSelection="'single'"
      :pagination="true"
      :paginationPageSize="10"
      :isRowSelectable="isRowSelectable"
      :enableCellTextSelection="true"
    >
    </ag-grid-vue>
  </div>
</template>

<script>
// Importar Librerias o Modulos
import { AgGridVue } from "ag-grid-vue";

export default {
  data() {
    return {
      defaultColDef: {
        editable: true,
        sortable: true,
        flex: 1,
        minWidth: 100,
        filter: true,
        resizable: true,
      },
      gridApi: null,
      columnApi: null,
      columnDefs: [],
      rowData: [],
      rowChange: {
        old: [],
      },
      isRowSelectable: null,
    };
  },
  components: {
    AgGridVue,
  },
  created() {
    this.loadSites();
  },
  mounted() {},
  methods: {
    loadSites() {
      this.columnDefs = [
        {
          headerName: "Seleccionar",
          field: "",
          pinned: "left",
          checkboxSelection: function () {
            return true;
          },
          headerCheckboxSelection: false,
          headerCheckboxSelectionFilteredOnly: true,
        },
        { headerName: "Sitio", field: "sitio" },
        { headerName: "Url", field: "url" },
      ];

      axios
        .get("/administration/sites")
        .then((data) => {
          this.rowData = data.data ? data.data : [];
          console.log(this.rowData);
        })
        .catch((error) => {
          this.$readStatusHttp(error);
        });
    },
  },
};
</script>