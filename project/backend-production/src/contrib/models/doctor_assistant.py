# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend


class DoctorAssistant(DocumentExtend):

    meta = {
        'collection': 'doctor_assistant',
    }

    name = db.StringField(required=True)
    mobile = db.StringField(required=True)

    def visible(self):
        return set(['name', 'mobile'])


# vim:ts=4:sw=4
