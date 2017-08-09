# vim: set fileencoding=utf-8 :


from .. import app


# from .views.rpc.endpoint import rpc_endpoint as bp_rpc_endpoint
from .rpc.user import rpc_user as bp_rpc_user
from .rpc.captcha import rpc_captcha as bp_rpc_captcha
from .rpc.doctor import rpc_doctor as bp_rpc_doctor
from .rpc.tag import rpc_tag as bp_rpc_tag
from .rpc.template import rpc_template as bp_rpc_template
from .rpc.patient import rpc_patient as bp_rpc_patient
from .rpc.order import rpc_order as bp_rpc_order
from .rpc.spinner import rpc_spinner as bp_rpc_spinner
from .rpc.release import rpc_release as bp_rpc_release
from .rpc.feedback import rpc_feedback as bp_rpc_feedback
from .rpc.hooks import rpc_hooks as bp_rpc_hooks
from .rpc.storage import rpc_storage as bp_rpc_storage

from .admin.doctor import admin_doctor as bp_admin_doctor
from .admin.article import admin_article as bp_admin_article
from .admin.hospital import admin_hospital as bp_admin_hospital
from .admin.department import admin_department as bp_admin_department
from .admin.cron import admin_cron as bp_admin_cron
from .admin.region import admin_region as bp_admin_region
from .admin.order import admin_order as bp_admin_order
from .admin.todo import admin_todo as bp_admin_todo
from .admin.doctor_bank import admin_doctor_bank as bp_admin_doctor_bank
from .admin.analytics import admin_analytics as bp_admin_analytics

from .about import About as bp_about

# app.register_blueprint(bp_rpc_endpoint)
app.register_blueprint(bp_rpc_user)
app.register_blueprint(bp_rpc_captcha)
app.register_blueprint(bp_rpc_doctor)
app.register_blueprint(bp_rpc_tag)
app.register_blueprint(bp_rpc_template)
app.register_blueprint(bp_rpc_patient)
app.register_blueprint(bp_rpc_order)
app.register_blueprint(bp_rpc_spinner)
app.register_blueprint(bp_rpc_release)
app.register_blueprint(bp_rpc_feedback)
app.register_blueprint(bp_rpc_hooks)
app.register_blueprint(bp_rpc_storage)

app.register_blueprint(bp_admin_doctor)
app.register_blueprint(bp_admin_article)
app.register_blueprint(bp_admin_hospital)
app.register_blueprint(bp_admin_department)
app.register_blueprint(bp_admin_cron)
app.register_blueprint(bp_admin_region)
app.register_blueprint(bp_admin_order)
app.register_blueprint(bp_admin_todo)
app.register_blueprint(bp_admin_doctor_bank)
app.register_blueprint(bp_admin_analytics)

app.register_blueprint(bp_about)


# vim:ts=4:sw=4
