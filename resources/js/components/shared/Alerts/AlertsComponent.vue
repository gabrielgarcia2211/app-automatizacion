<script>
export default {
    methods: {
        $alertSuccess(title, message, timer = 3500, position = 'top-end', showConfirmButton = false) {
            buildAlert(this, 'success', title, message, timer, position, showConfirmButton);
        },
        $alertInfo(title, message, timer = 3500, position = 'top-end', showConfirmButton = false) {
            buildAlert(this, 'info', title, message, timer, position, showConfirmButton);
        },
        $alertWarning(title, message, timer = 3500, position = 'top-end', showConfirmButton = false) {
            buildAlert(this, 'warning', title, message, timer, position, showConfirmButton);
        },
        $alertDanger(title, message, timer = 3500, position = 'top-end', showConfirmButton = false) {
            buildAlert(this, 'error', title, message, timer, position, showConfirmButton);
        },
        $alertFaildValidForm() {
            buildAlert(this, 'warning', 'Error de Formulario', 'El formulario esta incompleto o tiene datos incorrectos', 3500, 'top-end', false);
        }
    }
}

function buildAlert(self, icon, title, message, timer, position, showConfirmButton) {
    self.$swal.fire({
        position: position,
        icon: icon,
        title: title,
        text: message,
        showConfirmButton: showConfirmButton,
        timer: timer
    });
}
</script>