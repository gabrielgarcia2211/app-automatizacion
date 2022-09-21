<script>
import ab2str from 'arraybuffer-to-string';
const readStatus = {};

// Funcion para obtener los datos del error de las respuesta http
readStatus.getDataError = (error = []) => {
    let response = {};
        try {
            response = [error][0].response;
        } catch (error) {
            response = {};
        }
    return response;
}

readStatus.validateData = (data) => {
    // Validamos que el mensaje es de tipo string para retornar
    if (typeof(data) === 'string' && data.trim().length > 0) return data;

    // Si el mensaje es un ArrayBuffer convertimos a string
    if (typeof(data) === 'object') return ab2str(data);

    // Si el mensaje no es de tipo strin enviamos un mensaje predeterminado
    return 'No se pudo completar el proceso, por favor intente de nuevo';
}

export default {
    methods: {
        $readStatusHttp(error = []) {
            const response = readStatus.getDataError(error);
            
            switch(response.status) {
                case 400:
                    this.$alertDanger('Algo salio mal', readStatus.validateData(response.data));
                    break;
                case 401:
                    this.$alertDanger('Tu sesión expiró', "");
                    setTimeout(() => window.location.href = "/", 2000);
                    break;
                case 402:
                    this.$alertDanger('Algo salio mal', readStatus.validateData(response.data));
                    break;
                case 403:
                    this.$alertDanger('Proceso denegado', 'No tienes permiso para realizar este proceso');
                    break;
                case 404:
                    this.$alertDanger('Algo salio mal', readStatus.validateData(response.data));
                    break;
                case 406:
                    this.$alertDanger('Algo salio mal', readStatus.validateData(response.data));
                    break;
                case 409:
                    this.$alertDanger('Proceso no autorizado', 'El proceso no fue autorizado, intente con otro tipo de proceso');
                    break;
                case 419:
                    this.$alertDanger('Tu sesión expiró', "");
                    setTimeout(() => window.location.href = "/", 2000);
                    break;
                case 422:
                    this.$alertDanger('Error de formulario', 'Los datos proporcionados no son válidos.');
                    break;
                case 423:
                    this.$alertDanger('Limite Excedido', readStatus.validateData(response.data));
                    break;
                case 500:
                    this.$alertDanger('Algo salio mal', 'Ocurrió un error al realizar el proceso');
                    break;
                default:
                    this.$alertDanger('Algo salio mal', 'Ocurrió un error inesperado');
            }    
        }
    }
}
</script>