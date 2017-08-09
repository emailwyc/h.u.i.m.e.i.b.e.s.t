# vim: set fileencoding=utf-8 :

from flask.ext.mongoengine import MongoEngine
db = MongoEngine()

from .endpoint import Endpoint
from .session import Session
from .user import User
from .captcha import Captcha
from .doctor import Doctor
from .hospital import Hospital
from .department import Department
from .region import Region
from .doctor_qrcode import DoctorQRcode
from .doctor_location import DoctorLocation
from .doctor_timetable import DoctorTimetable
from .doctor_private import DoctorPrivate
from .doctor_article import DoctorArticle
from .article_hot import ArticleHot
from .doctor_assistant import DoctorAssistant
from .order_qrcode import OrderQrcode
from .tag import Tag
from .template import Template
from .patient import Patient
from .relation import Relation
from .patient_family import PatientFamily
from .order import Order
from .order_comment import OrderComment
from .feedback import Feedback
from .release import Release
from .voip_log import VoIPLog
from .setting import Setting

# vim:ts=4:sw=4
