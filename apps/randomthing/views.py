from rest_framework.viewsets import ModelViewSet
from apps.randomthing.models import Person
from apps.randomthing.serializers import PersonSerializer


class PersonViewSet(ModelViewSet):
    queryset = Person.objects.all()
    serializer_class = PersonSerializer
