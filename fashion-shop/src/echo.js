import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'pusher',
    key: '6b4e5bbb068bd2870452', 
    cluster: 'ap1',              
    forceTLS: true               
});

export default echo;
