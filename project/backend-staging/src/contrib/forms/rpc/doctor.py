# vim: set fileencoding=utf-8 :

from . import *
from werkzeug.datastructures import MultiDict, ImmutableMultiDict


class EmbeddedDictField(Field):
    def process_formdata(self, valuelist):
        self.data = None
        if not valuelist:
            return None
        vl = ImmutableMultiDict(valuelist[0])
        if self.name == 'bank_card':
            _form = _bank_card_form(vl)
        elif self.name == 'service_provided':
            _form = _service_provided_form(vl)
        else:
            return None
        if _form.validate():
            self.data = _form.data
        else:
            raise validators.ValidationError(_form.errors)


class _bank_card_form(Form):
    bank = StringField('bank')
    city = StringField('city')
    branch = StringField('branch')
    card = StringField('card')


class _service_provided_form(Form):
    clinic = StringField('clinic')
    consult = StringField('consult')
    phonecall = StringField('phonecall')


fields = {
    'name': StringField('name'),
    'avatar': StringField('avatar'),
    'hospital': StringField('hospital'),
    'department': StringField('department'),
    'position': StringField('position'),
    'title': StringField('title'),
    'speciality': StringField('speciality'),
    'description': StringField('description'),
    'starred': IntegerField('starred'),
    'service_provided': EmbeddedDictField('service_provided'),
    'locations': ArrayField('locations'),
    'idcard': StringField('speciality'),
    'certificate': URLArrayField('certificate'),
    'bank_card': EmbeddedDictField('bank_card'),
    'month': StringField('month', validators=[
        validators.DataRequired(message='年月必填')
    ]),
}


class InfoForm(Form):
    mobile = fields.get('mobile')
    name = fields.get('name')
    avatar = fields.get('avatar')
    hospital = fields.get('hospital')
    department = fields.get('department')
    position = fields.get('position')
    title = fields.get('title')
    speciality = fields.get('speciality')
    description = fields.get('description')
    service_provided = fields.get('service_provided')
    locations = fields.get('locations')


class InfoSpecialityForm(Form):
    speciality = fields.get('speciality')


class InfoDescriptionForm(Form):
    description = fields.get('description')


class InfoServiceProvidedForm(Form):
    service_provided = fields.get('service_provided')


class TimetableFilterForm(Form):
    service = StringField('service', validators=[
        validators.DataRequired(message='服务类型必填'),
        validators.AnyOf(('clinic', 'phonecall'), message='服务类型错误')
    ])


class TimetableCreateForm(Form):
    service = StringField('service', validators=[
        validators.DataRequired(message='服务类型必填'),
        validators.AnyOf(('clinic', 'phonecall'), message='服务类型错误')
    ])
    date = StringField('date', validators=[
        validators.DataRequired(message='日期格式错误，示例：2006-01-02')
    ])
    interval = StringField('interval', validators=[
        validators.DataRequired(message='起止时间必填')
    ])
    weekday = StringField('weekday', validators=[
        validators.DataRequired(message='星期必填'),
        validators.AnyOf(('1', '2', '3', '4', '5', '6', '7'), message='星期错误')
    ])
    price = DecimalField('price')
    quantity = IntegerField('quantity')
    location = ObjectIdField('location')


class TimetableEditForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])
    interval = StringField('interval')
    price = DecimalField('price')
    quantity = IntegerField('quantity')
    location = ObjectIdField('location')


class TimetableDeleteForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])


class PrivateForm(Form):
    idcard = fields.get('idcard')
    certificate = fields.get('certificate')
    bank_card = fields.get('bank_card')


class RevenueForm(Form):
    month = fields.get('month')


class SecurityCodeSetForm(Form):
    security_code = StringField('security_code', validators=[
        validators.DataRequired(message='安全密码必填')
    ])


class SecurityCodeCheckForm(Form):
    security_code = StringField('security_code', validators=[
        validators.DataRequired(message='安全密码必填')
    ])


class LocationCreateForm(Form):
    hospital = StringField('hospital', validators=[
        validators.DataRequired(message='医院必填')
    ])
    branch = StringField('branch')
    address = StringField('address', validators=[
        validators.DataRequired(message='地址必填')
    ])
    info = StringField('info')


class LocationEditForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])
    hospital = StringField('hospital', validators=[
        validators.DataRequired(message='医院必填')
    ])
    branch = StringField('branch')
    address = StringField('address', validators=[
        validators.DataRequired(message='地址必填')
    ])
    info = StringField('info')


class LocationDeleteForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])


class ArticleListForm(Form):
    since_article_id = ObjectIdField('since_article_id', validators=[
        validators.Optional(strip_whitespace=True)
    ])


# vim:ts=4
