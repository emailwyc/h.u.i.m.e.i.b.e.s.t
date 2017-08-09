# vim: set fileencoding=utf-8 :

from . import *
from flask_wtf import Form


fields = {
    'mobile': StringField('手机', validators=[
        validators.Regexp(r'^1[3578][0-9]{9}$', message='手机格式错误'),
        validators.DataRequired(message='手机必填')
    ]),
    'freeze': SelectField('是否显示', choices=[('no', '显示'), ('yes', '不显示')], validators=[
        validators.DataRequired(message='是否显示必填')
    ]),
    'name': StringField('姓名', filters=(strip, ), validators=[
        validators.DataRequired(message='姓名必填')
    ]),
    'assistant': SelectField('医生助理', choices=[], validators=[
        validators.DataRequired(message='医生助理必填')
    ]),
    'mul_num': IntegerField('综合排序值'),
    'avatar': FileField('头像', validators=[
        FileRequired('头像必填'),
        FileAllowed(['jpg', 'png'], '只支持 jpg, png 格式的图片')
    ]),
    'hospital': SelectField('医院', choices=[], validators=[
        validators.DataRequired(message='医院必填')
    ]),
    'department': StringField('详细科室', filters=(strip, ), validators=[
        validators.DataRequired(message='详细科室必填')
    ]),
    'department_id': SelectField('一级科室', choices=[], validators=[
        validators.DataRequired(message='一级科室必填')
    ]),
    'department_child_id': SelectField('二级科室', choices=[], validators=[
        validators.DataRequired(message='二级科室必填')
    ]),
    'position': SelectField('职称', choices=[], validators=[
        validators.DataRequired(message='职称必填')
    ]),
    'title': SelectField('头衔', choices=[], validators=[
        validators.DataRequired(message='头衔必填')
    ]),
    'speciality': TextAreaField('擅长', filters=(strip, )),
    'description': TextAreaField('简介', filters=(strip, )),
    'locations': TextAreaField('出诊<br>地点', filters=(strip, ), validators=[
        validators.DataRequired(message='出诊地点必填')
    ]),
    'articles': TextAreaField('患教<br>文章', filters=(strip, )),
}


class CreateForm(Form):
    mobile = fields.get('mobile')
    freeze = fields.get('freeze')
    name = fields.get('name')
    assistant = fields.get('assistant')
    mul_num = fields.get('mul_num')
    avatar = FileField('头像', validators=[
        FileAllowed(['jpg', 'png'], '只支持 jpg, png 格式的图片')
    ])
    hospital = fields.get('hospital')
    department = fields.get('department')
    department_id = fields.get('department_id')
    department_child_id = fields.get('department_child_id')
    position = fields.get('position')
    title = fields.get('title')
    speciality = fields.get('speciality')
    description = fields.get('description')
    # locations = fields.get('locations')


class UpdateForm(Form):
    mobile = fields.get('mobile')
    freeze = fields.get('freeze')
    name = fields.get('name')
    assistant = fields.get('assistant')
    mul_num = fields.get('mul_num')
    avatar = FileField('头像', validators=[
        FileAllowed(['jpg', 'png'], '只支持 jpg, png 格式的图片')
    ])
    hospital = fields.get('hospital')
    department = fields.get('department')
    department_id = fields.get('department_id')
    department_child_id = fields.get('department_child_id')
    position = fields.get('position')
    title = fields.get('title')
    speciality = fields.get('speciality')
    description = fields.get('description')
    # locations = fields.get('locations')


class ArticleForm(Form):
    articles = fields.get('articles')


# vim:ts=4:sw=4
