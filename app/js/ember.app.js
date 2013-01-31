window.App = Ember.Application.create();

window.App.Router.map(function() {
    this.route("about");
});

window.App.AboutController = Ember.Controller.extend({
    fname: 'About!!'
});
window.App.AboutRoute = Ember.Route.extend({
    renderTemplate: function() {
        this.render('about-template');
    }
});
