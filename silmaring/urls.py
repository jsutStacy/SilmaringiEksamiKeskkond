from django.conf.urls import patterns, include, url

from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    # Examples:
    # url(r'^$', 'silmaring.views.home', name='home'),
    # url(r'^blog/', include('blog.urls')),
    url(r'^api/randomthing/', include('apps.randomthing.urls')),
    url(r'^admin/', include(admin.site.urls)),
)
