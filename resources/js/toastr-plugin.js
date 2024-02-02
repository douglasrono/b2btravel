import 'toastr/toastr.scss';
import toastr from 'toastr/toastr';

const ToastrPlugin = {};

ToastrPlugin.install = (Vue) => {
    Vue.mixin({
        beforeCreate() {
            this.$toastr = {
                success: (message, title = 'Success') => {
                    toastr.success(message, title, { timeOut: 1000 });
                },
                error: (message, title = 'Error') => {
                    toastr.error(message, title, { timeOut: 1000 });
                },
            };
        },
    });
};

export default ToastrPlugin;
