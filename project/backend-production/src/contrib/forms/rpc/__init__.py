# vim: set fileencoding=utf-8 :

from wtforms import Form, Field
from wtforms import BooleanField, DecimalField, IntegerField, DateField, DateTimeField
from wtforms import StringField, TextField, SelectField, SelectMultipleField
from wtforms import validators
from datetime import datetime
from bson import ObjectId
from ... import SERVICE_TYPE


class ObjectIdField(Field):
    def process_formdata(self, valuelist):
        self.data = ''
        if not valuelist:
            return None
        vl = valuelist[0]
        try:
            self.data = ObjectId(vl)
        except Exception as e:
            self.data = None
            errors = {'id': ['id 错误: {0}'.format(e)]}
            raise validators.ValidationError(errors)


class ObjectIdArrayField(Field):
    def process_formdata(self, valuelist):
        self.data = []
        if not valuelist:
            self.data = None
            return None
        for vl in valuelist:
            try:
                self.data.append(ObjectId(vl))
            except Exception as e:
                self.data = None
                errors = {'ids': ['id 错误: {0}'.format(e)]}
                raise validators.ValidationError(errors)


class DateTimeAsUTCField(DateTimeField):
    def process_formdata(self, valuelist):
        if valuelist:
            date_str = ' '.join(valuelist)
            if len(date_str) < 25:
                date_str += ' +0800'
            try:
                self.data = datetime.strptime(date_str, self.format)
            except ValueError:
                self.data = None
                raise ValueError(self.gettext('Not a valid datetime value'))


class URLArrayField(Field):
    def process_formdata(self, valuelist):
        self.data = []
        if not valuelist:
            self.data = None
            return None
        for vl in valuelist:
            if isinstance(vl, str) and (vl.startswith('http://') or vl.startswith('https://')):
                self.data.append(vl)
            else:
                self.data = None
                errors = {'certificate': ['非法的URL地址']}
                raise validators.ValidationError(errors)


class OriginBooleanField(Field):
    def process_formdata(self, valuelist):
        if not valuelist:
            return None
        vl = valuelist[0]
        if isinstance(vl, bool):
            self.data = vl
            return True
        errors = {self.name: ['{0} 必须是布尔型'.format(self.name)]}
        raise validators.ValidationError(errors)


class ArrayField(Field):
    def process_formdata(self, valuelist):
        self.data = []
        if not valuelist:
            return []
        if isinstance(valuelist, list):
            self.data = [str(vl) for vl in valuelist]
            return True
        errors = {self.name: ['{0} 必须是布尔型'.format(self.name)]}
        raise validators.ValidationError(errors)


# vim:ts=4:sw=4
