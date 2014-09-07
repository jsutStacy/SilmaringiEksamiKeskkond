
from django.conf.urls import patterns, include, url
from rest_framework import routers
from apps.randomthing.views import PersonViewSet

router = routers.DefaultRouter()
router.register(r'randomthing', PersonViewSet)

urlpatterns = patterns('',
    url(r'^', include(router.urls)),
)