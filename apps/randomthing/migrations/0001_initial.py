# -*- coding: utf-8 -*-
from south.utils import datetime_utils as datetime
from south.db import db
from south.v2 import SchemaMigration
from django.db import models


class Migration(SchemaMigration):

    def forwards(self, orm):
        # Adding model 'Person'
        db.create_table('randomthing_person', (
            ('id', self.gf('django.db.models.fields.AutoField')(primary_key=True)),
            ('first_name', self.gf('django.db.models.fields.CharField')(max_length=20, blank=True)),
            ('family_name', self.gf('django.db.models.fields.CharField')(max_length=50)),
        ))
        db.send_create_signal('randomthing', ['Person'])


    def backwards(self, orm):
        # Deleting model 'Person'
        db.delete_table('randomthing_person')


    models = {
        'randomthing.person': {
            'Meta': {'object_name': 'Person'},
            'family_name': ('django.db.models.fields.CharField', [], {'max_length': '50'}),
            'first_name': ('django.db.models.fields.CharField', [], {'max_length': '20', 'blank': 'True'}),
            'id': ('django.db.models.fields.AutoField', [], {'primary_key': 'True'})
        }
    }

    complete_apps = ['randomthing']