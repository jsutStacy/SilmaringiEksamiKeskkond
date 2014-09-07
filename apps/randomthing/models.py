from django.db import models


class Person(models.Model):
    first_name = models.CharField(blank=True, max_length=20)
    family_name = models.CharField(max_length=50)
