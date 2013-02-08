var App = Ember.Application.create();

App.Store = DS.Store.extend({
    revision: 11,
});
App.Post = DS.Model.extend({
    id: DS.attr('number')
});

App.Router.map(function() {
    this.route("about");
});

App.AboutController = Ember.Controller.extend({
    fname: 'Wtf!!'
});
App.AboutRoute = Ember.Route.extend({
    renderTemplate: function() {
        this.render('about-template');
    }
});
