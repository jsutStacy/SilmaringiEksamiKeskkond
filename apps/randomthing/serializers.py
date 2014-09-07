from rest_framework import serializers
from apps.randomthing.models import Person


class PersonSerializer(serializers.HyperlinkedModelSerializer):
    
    class Meta():
        model = Person